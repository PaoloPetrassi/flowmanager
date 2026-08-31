<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Services\PdfReportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly PdfReportService $pdfReports
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeView($request);

        $type = $this->validatedType($request);
        [$dateFrom, $dateTo] = $this->validatedDates($request);
        $rows = $this->reports->rows($type, $dateFrom, $dateTo);

        return view('reports.index', [
            'type' => $type,
            'types' => ReportService::TYPES,
            'columns' => $this->reports->columns($type),
            'rows' => $rows->take(200),
            'totalRows' => $rows->count(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $this->summary(),
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);

        $type = $this->validatedType($request);
        [$dateFrom, $dateTo] = $this->validatedDates($request);
        $columns = $this->reports->columns($type);
        $rows = $this->reports->rows($type, $dateFrom, $dateTo);
        $filename = 'flowmanager-'.$type.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($columns, $rows): void {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_values($columns), ';', '"', '');

            foreach ($rows as $row) {
                fputcsv($handle, array_map(
                    fn ($key) => $row[$key] ?? '',
                    array_keys($columns)
                ), ';', '"', '');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function excel(Request $request): Response
    {
        $this->authorizeExport($request);

        $type = $this->validatedType($request);
        [$dateFrom, $dateTo] = $this->validatedDates($request);
        $columns = $this->reports->columns($type);
        $rows = $this->reports->rows($type, $dateFrom, $dateTo);
        $filename = 'flowmanager-'.$type.'-'.now()->format('Ymd-His').'.xls';

        $xml = view('reports.excel', [
            'sheetName' => $this->reports->label($type),
            'columns' => $columns,
            'rows' => $rows,
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
        ]);
    }

    public function pdf(Request $request): Response
    {
        $this->authorizeExport($request);

        $type = $this->validatedType($request);
        [$dateFrom, $dateTo] = $this->validatedDates($request);
        $label = $this->reports->label($type);
        $rows = $this->reports->rows($type, $dateFrom, $dateTo);
        $filename = 'flowmanager-'.$type.'-'.now()->format('Ymd-His').'.pdf';
        $period = $this->periodLabel($dateFrom, $dateTo);

        $pdf = $this->pdfReports->render(
            'FlowManager · '.$label,
            $this->reports->columns($type),
            $rows,
            collect([
                __('Generated at :date', ['date' => now()->format('d/m/Y H:i')]),
                $period,
            ])->filter()->implode(' · ')
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($pdf),
        ]);
    }

    public function print(Request $request): View
    {
        $this->authorizeExport($request);

        $type = $this->validatedType($request);
        [$dateFrom, $dateTo] = $this->validatedDates($request);

        return view('reports.print', [
            'type' => $type,
            'label' => $this->reports->label($type),
            'columns' => $this->reports->columns($type),
            'rows' => $this->reports->rows($type, $dateFrom, $dateTo),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    private function validatedType(Request $request): string
    {
        $type = (string) $request->query('type', 'projects');

        return in_array($type, ReportService::TYPES, true)
            ? $type
            : 'projects';
    }

    private function validatedDates(Request $request): array
    {
        $dateToRules = ['nullable', 'date'];

        if ($request->filled('date_from')) {
            $dateToRules[] = 'after_or_equal:date_from';
        }

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => $dateToRules,
        ]);

        return [
            $validated['date_from'] ?? null,
            $validated['date_to'] ?? null,
        ];
    }

    private function authorizeView(Request $request): void
    {
        abort_unless(
            $request->user()->hasPermission('reports.view'),
            403
        );
    }

    private function authorizeExport(Request $request): void
    {
        abort_unless(
            $request->user()->hasPermission('reports.export'),
            403
        );
    }

    private function summary(): array
    {
        return [
            'projects_active' => Project::query()->operational()->where('status', ProjectStatus::Active->value)->count(),
            'tasks_open' => Task::query()->operational()->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count(),
            'tasks_overdue' => Task::query()->operational()->overdue()->count(),
            'tickets_open' => Ticket::query()->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count(),
            'assets_active' => Asset::query()->where('status', '!=', AssetStatus::Retired->value)->count(),
        ];
    }

    private function periodLabel(?string $dateFrom, ?string $dateTo): ?string
    {
        if (! $dateFrom && ! $dateTo) {
            return null;
        }

        return __('Period').': '.($dateFrom ?: '—').' – '.($dateTo ?: '—');
    }
}
