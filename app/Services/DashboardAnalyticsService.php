<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class DashboardAnalyticsService
{
    /**
     * @return array{key:string,start:CarbonImmutable,end:CarbonImmutable,label:string,date_from:string,date_to:string}
     */
    public function period(string $preset, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $today = CarbonImmutable::today();
        $key = in_array($preset, ['7d', '30d', '90d', 'ytd', 'custom'], true) ? $preset : '30d';

        [$start, $end] = match ($key) {
            '7d' => [$today->subDays(6)->startOfDay(), $today->endOfDay()],
            '90d' => [$today->subDays(89)->startOfDay(), $today->endOfDay()],
            'ytd' => [$today->startOfYear(), $today->endOfDay()],
            'custom' => $this->customPeriod($dateFrom, $dateTo, $today),
            default => [$today->subDays(29)->startOfDay(), $today->endOfDay()],
        };

        return [
            'key' => $key,
            'start' => $start,
            'end' => $end,
            'label' => $start->locale(app()->getLocale())->translatedFormat('d M Y').' – '.$end->locale(app()->getLocale())->translatedFormat('d M Y'),
            'date_from' => $start->toDateString(),
            'date_to' => $end->toDateString(),
        ];
    }

    /**
     * @return array<int, array{key:string,label:string,value:string|int,icon:string,help:string}>
     */
    public function kpis(User $user, array $period): array
    {
        $items = [];

        if (Gate::forUser($user)->allows('viewAny', Task::class)) {
            $completedTasks = Task::query()
                ->operational()
                ->whereBetween('completed_at', [$period['start'], $period['end']])
                ->count();

            $items[] = [
                'key' => 'completed_tasks',
                'label' => __('Tasks completed'),
                'value' => $completedTasks,
                'icon' => 'bi-check2-circle',
                'help' => __('Completed inside the selected period'),
            ];
        }

        if (Gate::forUser($user)->allows('viewAny', Ticket::class)) {
            $resolvedTickets = Ticket::query()
                ->whereBetween('resolved_at', [$period['start'], $period['end']])
                ->get(['created_at', 'resolved_at', 'sla_due_at']);

            $averageResolutionMinutes = (int) round($resolvedTickets
                ->filter(fn (Ticket $ticket) => $ticket->created_at && $ticket->resolved_at)
                ->avg(fn (Ticket $ticket) => $ticket->created_at->diffInMinutes($ticket->resolved_at)) ?? 0);

            $slaBreaches = $resolvedTickets
                ->filter(fn (Ticket $ticket) => $ticket->sla_due_at && $ticket->resolved_at?->isAfter($ticket->sla_due_at))
                ->count();

            $items[] = [
                'key' => 'resolved_tickets',
                'label' => __('Tickets resolved'),
                'value' => $resolvedTickets->count(),
                'icon' => 'bi-ticket-detailed',
                'help' => __('Resolved inside the selected period'),
            ];

            $items[] = [
                'key' => 'average_resolution',
                'label' => __('Average resolution time'),
                'value' => $this->durationLabel($averageResolutionMinutes),
                'icon' => 'bi-stopwatch',
                'help' => __('Average from ticket creation to resolution'),
            ];

            $items[] = [
                'key' => 'sla_breaches',
                'label' => __('Resolved after SLA'),
                'value' => $slaBreaches,
                'icon' => 'bi-exclamation-octagon',
                'help' => __('Resolved tickets that exceeded their SLA deadline'),
            ];
        }

        if (Gate::forUser($user)->allows('viewAny', Project::class)) {
            $items[] = [
                'key' => 'completed_projects',
                'label' => __('Projects completed'),
                'value' => Project::query()
                    ->operational()
                    ->where('status', ProjectStatus::Completed->value)
                    ->whereBetween('updated_at', [$period['start'], $period['end']])
                    ->count(),
                'icon' => 'bi-flag',
                'help' => __('Projects completed inside the selected period'),
            ];
        }

        return $items;
    }

    /**
     * @return array{items:array<int,array{key:string,label:string,tasks:int,tickets:int}>,max:int,granularity:string}
     */
    public function trend(User $user, array $period): array
    {
        $days = max(1, $period['start']->diffInDays($period['end']) + 1);
        $granularity = $days <= 14 ? 'day' : ($days <= 120 ? 'week' : 'month');
        $buckets = $this->buckets($period['start'], $period['end'], $granularity);

        $completedTasks = Gate::forUser($user)->allows('viewAny', Task::class)
            ? Task::query()
                ->operational()
                ->whereBetween('completed_at', [$period['start'], $period['end']])
                ->get(['completed_at'])
                ->groupBy(fn (Task $task) => $this->bucketKey($task->completed_at, $granularity))
                ->map->count()
            : collect();

        $resolvedTickets = Gate::forUser($user)->allows('viewAny', Ticket::class)
            ? Ticket::query()
                ->whereBetween('resolved_at', [$period['start'], $period['end']])
                ->get(['resolved_at'])
                ->groupBy(fn (Ticket $ticket) => $this->bucketKey($ticket->resolved_at, $granularity))
                ->map->count()
            : collect();

        $items = $buckets->map(function (CarbonImmutable $bucket) use ($completedTasks, $resolvedTickets, $granularity) {
            $key = $this->bucketKey($bucket, $granularity);

            return [
                'key' => $key,
                'label' => $this->bucketLabel($bucket, $granularity),
                'tasks' => (int) ($completedTasks[$key] ?? 0),
                'tickets' => (int) ($resolvedTickets[$key] ?? 0),
            ];
        });

        return [
            'items' => $items->all(),
            'max' => max(1, (int) $items->max('tasks'), (int) $items->max('tickets')),
            'granularity' => $granularity,
        ];
    }

    /**
     * @return Collection<int, AuditLog>
     */
    public function activity(User $user, array $period): Collection
    {
        if (! $user->hasPermission('audit.view')) {
            return collect();
        }

        return AuditLog::query()
            ->with('user:id,name,email')
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->latest('created_at')
            ->limit(14)
            ->get();
    }

    /**
     * @return array{0:CarbonImmutable,1:CarbonImmutable}
     */
    private function customPeriod(?string $dateFrom, ?string $dateTo, CarbonImmutable $today): array
    {
        try {
            if (! $this->isDate($dateFrom) || ! $this->isDate($dateTo)) {
                throw new \InvalidArgumentException();
            }

            $start = CarbonImmutable::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
            $end = CarbonImmutable::createFromFormat('Y-m-d', $dateTo)->endOfDay();

            if ($start->isAfter($end) || $start->diffInDays($end) > 366) {
                throw new \InvalidArgumentException();
            }

            return [$start, $end];
        } catch (\Throwable) {
            return [$today->subDays(29)->startOfDay(), $today->endOfDay()];
        }
    }

    private function isDate(?string $value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    private function buckets(CarbonImmutable $start, CarbonImmutable $end, string $granularity): Collection
    {
        $cursor = match ($granularity) {
            'week' => $start->startOfWeek(),
            'month' => $start->startOfMonth(),
            default => $start->startOfDay(),
        };

        $last = match ($granularity) {
            'week' => $end->startOfWeek(),
            'month' => $end->startOfMonth(),
            default => $end->startOfDay(),
        };

        $items = collect();

        while ($cursor->lte($last)) {
            $items->push($cursor);
            $cursor = match ($granularity) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        return $items;
    }

    private function bucketKey(CarbonInterface $date, string $granularity): string
    {
        return match ($granularity) {
            'week' => $date->copy()->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function bucketLabel(CarbonImmutable $date, string $granularity): string
    {
        $localized = $date->locale(app()->getLocale());

        return match ($granularity) {
            'week' => $localized->translatedFormat('d M'),
            'month' => $localized->translatedFormat('M y'),
            default => $localized->translatedFormat('d M'),
        };
    }

    private function durationLabel(int $minutes): string
    {
        if ($minutes <= 0) {
            return '—';
        }

        if ($minutes < 60) {
            return __(':minutes min', ['minutes' => $minutes]);
        }

        $hours = round($minutes / 60, 1);

        return __(':hours h', ['hours' => $hours]);
    }
}
