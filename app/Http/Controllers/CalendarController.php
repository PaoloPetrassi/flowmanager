<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->resolveMonth((string) $request->query('month'));
        $start = $month->startOfMonth();
        $end = $month->endOfMonth();
        $events = collect();

        if (Gate::allows('viewAny', Project::class)) {
            Project::query()
                ->operational()
                ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
                ->with('company:id,name')
                ->orderBy('due_date')
                ->get()
                ->each(function (Project $project) use ($events): void {
                    $events->push([
                        'date' => $project->due_date->toDateString(),
                        'type' => 'project',
                        'label' => $project->name,
                        'meta' => collect([$project->code, $project->company?->name])->filter()->implode(' · '),
                        'url' => route('projects.show', $project),
                        'icon' => 'bi-kanban',
                    ]);
                });
        }

        if (Gate::allows('viewAny', Task::class)) {
            Task::query()
                ->operational()
                ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
                ->with('project:id,code,name')
                ->orderBy('due_date')
                ->get()
                ->each(function (Task $task) use ($events): void {
                    $events->push([
                        'date' => $task->due_date->toDateString(),
                        'type' => 'task',
                        'label' => $task->title,
                        'meta' => $task->project?->code,
                        'url' => route('tasks.show', $task),
                        'icon' => 'bi-check2-square',
                    ]);
                });
        }

        if (Gate::allows('viewAny', Asset::class)) {
            Asset::query()
                ->whereBetween('warranty_expires_at', [$start->toDateString(), $end->toDateString()])
                ->orderBy('warranty_expires_at')
                ->get()
                ->each(function (Asset $asset) use ($events): void {
                    $events->push([
                        'date' => $asset->warranty_expires_at->toDateString(),
                        'type' => 'asset',
                        'label' => $asset->name,
                        'meta' => $asset->asset_tag,
                        'url' => route('assets.show', $asset),
                        'icon' => 'bi-laptop',
                    ]);
                });
        }

        $eventsByDate = $events
            ->groupBy('date')
            ->map(fn (Collection $items) => $items->values());

        $calendarStart = $start->startOfWeek(CarbonImmutable::MONDAY);
        $calendarEnd = $end->endOfWeek(CarbonImmutable::SUNDAY);
        $days = collect();

        for ($day = $calendarStart; $day->lte($calendarEnd); $day = $day->addDay()) {
            $days->push($day);
        }

        return view('calendar.index', [
            'month' => $month,
            'days' => $days,
            'eventsByDate' => $eventsByDate,
            'previousMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->addMonth()->format('Y-m'),
            'eventCount' => $events->count(),
        ]);
    }

    public function ics(): Response
    {
        $events = collect();

        if (Gate::allows('viewAny', Project::class)) {
            Project::query()->operational()->whereNotNull('due_date')->get()->each(function (Project $project) use ($events): void {
                $events->push(['uid' => 'project-'.$project->id, 'date' => $project->due_date, 'summary' => $project->code.' - '.$project->name, 'url' => route('projects.show', $project)]);
            });
        }

        if (Gate::allows('viewAny', Task::class)) {
            Task::query()->operational()->whereNotNull('due_date')->get()->each(function (Task $task) use ($events): void {
                $events->push(['uid' => 'task-'.$task->id, 'date' => $task->due_date, 'summary' => $task->title, 'url' => route('tasks.show', $task)]);
            });
        }

        $escape = fn (string $value) => str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', ''], $value);
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//FlowManager//Calendar//EN', 'CALSCALE:GREGORIAN'];

        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$event['uid'].'@flowmanager';
            $lines[] = 'DTSTAMP:'.now()->utc()->format('Ymd\\THis\\Z');
            $lines[] = 'DTSTART;VALUE=DATE:'.$event['date']->format('Ymd');
            $lines[] = 'SUMMARY:'.$escape($event['summary']);
            $lines[] = 'URL:'.$event['url'];
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="flowmanager-calendar.ics"',
        ]);
    }

    private function resolveMonth(string $value): CarbonImmutable
    {
        if (preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
            try {
                return CarbonImmutable::createFromFormat('Y-m', $value)->startOfMonth();
            } catch (\Throwable) {
                // Fall back to the current month.
            }
        }

        return CarbonImmutable::now()->startOfMonth();
    }
}
