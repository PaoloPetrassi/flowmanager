<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ReportService
{
    public const TYPES = [
        'projects',
        'tasks',
        'tickets',
        'assets',
    ];

    public function rows(
        string $type,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): Collection {
        return match ($type) {
            'projects' => $this->projectRows($dateFrom, $dateTo),
            'tasks' => $this->taskRows($dateFrom, $dateTo),
            'tickets' => $this->ticketRows($dateFrom, $dateTo),
            'assets' => $this->assetRows($dateFrom, $dateTo),
            default => throw new InvalidArgumentException('Unsupported report type.'),
        };
    }

    public function columns(string $type): array
    {
        return match ($type) {
            'projects' => [
                'code' => __('Code'),
                'name' => __('Project'),
                'company' => __('Company'),
                'manager' => __('Manager'),
                'status' => __('Status'),
                'priority' => __('Priority'),
                'start_date' => __('Start date'),
                'due_date' => __('Due date'),
                'budget' => __('Budget'),
            ],
            'tasks' => [
                'title' => __('Task'),
                'project' => __('Project'),
                'company' => __('Company'),
                'assignee' => __('Assigned to'),
                'status' => __('Status'),
                'priority' => __('Priority'),
                'due_date' => __('Due date'),
                'completed_at' => __('Completed at'),
            ],
            'tickets' => [
                'reference' => __('Reference'),
                'subject' => __('Subject'),
                'company' => __('Company'),
                'assignee' => __('Assigned to'),
                'category' => __('Category'),
                'status' => __('Status'),
                'priority' => __('Priority'),
                'created_at' => __('Created at'),
                'resolved_at' => __('Resolved at'),
            ],
            'assets' => [
                'asset_tag' => __('Asset tag'),
                'name' => __('Asset'),
                'company' => __('Company'),
                'assignee' => __('Assigned to'),
                'category' => __('Category'),
                'status' => __('Status'),
                'purchase_date' => __('Purchase date'),
                'purchase_cost' => __('Purchase cost'),
                'warranty_expires_at' => __('Warranty expires'),
            ],
            default => throw new InvalidArgumentException('Unsupported report type.'),
        };
    }

    public function label(string $type): string
    {
        return match ($type) {
            'projects' => __('Projects'),
            'tasks' => __('Tasks'),
            'tickets' => __('Tickets'),
            'assets' => __('Assets'),
            default => ucfirst($type),
        };
    }

    private function projectRows(?string $dateFrom, ?string $dateTo): Collection
    {
        return Project::query()
            ->with(['company:id,name', 'manager:id,name'])
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderBy('code')
            ->get()
            ->map(fn (Project $project) => [
                'code' => $project->code,
                'name' => $project->name,
                'company' => $project->company?->name,
                'manager' => $project->manager?->name,
                'status' => $project->status->label(),
                'priority' => $project->priority->label(),
                'start_date' => $project->start_date?->format('d/m/Y'),
                'due_date' => $project->due_date?->format('d/m/Y'),
                'budget' => $project->budget !== null
                    ? number_format((float) $project->budget, 2, ',', '.')
                    : null,
            ]);
    }

    private function taskRows(?string $dateFrom, ?string $dateTo): Collection
    {
        return Task::query()
            ->with(['project:id,code,name,company_id', 'project.company:id,name', 'assignee:id,name'])
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->get()
            ->map(fn (Task $task) => [
                'title' => $task->title,
                'project' => $task->project?->code.' · '.$task->project?->name,
                'company' => $task->project?->company?->name,
                'assignee' => $task->assignee?->name,
                'status' => $task->status->label(),
                'priority' => $task->priority->label(),
                'due_date' => $task->due_date?->format('d/m/Y'),
                'completed_at' => $task->completed_at?->format('d/m/Y H:i'),
            ]);
    }

    private function ticketRows(?string $dateFrom, ?string $dateTo): Collection
    {
        return Ticket::query()
            ->with(['company:id,name', 'assignee:id,name'])
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at')
            ->get()
            ->map(fn (Ticket $ticket) => [
                'reference' => $ticket->reference,
                'subject' => $ticket->subject,
                'company' => $ticket->company?->name,
                'assignee' => $ticket->assignee?->name,
                'category' => $ticket->category->label(),
                'status' => $ticket->status->label(),
                'priority' => $ticket->priority->label(),
                'created_at' => $ticket->created_at?->format('d/m/Y H:i'),
                'resolved_at' => $ticket->resolved_at?->format('d/m/Y H:i'),
            ]);
    }

    private function assetRows(?string $dateFrom, ?string $dateTo): Collection
    {
        return Asset::query()
            ->with(['company:id,name', 'assignee:id,name'])
            ->when($dateFrom, fn (Builder $query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn (Builder $query) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderBy('asset_tag')
            ->get()
            ->map(fn (Asset $asset) => [
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
                'company' => $asset->company?->name,
                'assignee' => $asset->assignee?->name,
                'category' => $asset->category,
                'status' => $asset->status->label(),
                'purchase_date' => $asset->purchase_date?->format('d/m/Y'),
                'purchase_cost' => $asset->purchase_cost !== null
                    ? number_format((float) $asset->purchase_cost, 2, ',', '.')
                    : null,
                'warranty_expires_at' => $asset->warranty_expires_at?->format('d/m/Y'),
            ]);
    }
}
