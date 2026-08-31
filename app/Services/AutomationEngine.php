<?php

namespace App\Services;

use App\Enums\AutomationAction;
use App\Enums\AutomationTrigger;
use App\Enums\ProjectStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\FlowNotification;
use App\Support\FlowResourceRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AutomationEngine
{
    public function run(): array
    {
        $summary = ['rules' => 0, 'executed' => 0, 'skipped' => 0, 'failed' => 0];

        AutomationRule::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->each(function (AutomationRule $rule) use (&$summary) {
                $summary['rules']++;

                foreach ($this->subjectsFor($rule->trigger) as $subject) {
                    if (! $this->matches($subject, $rule->conditions ?? [])) {
                        $summary['skipped']++;
                        continue;
                    }

                    if ($this->alreadyRanToday($rule, $subject)) {
                        $summary['skipped']++;
                        continue;
                    }

                    try {
                        $message = $this->execute($rule, $subject);
                        $this->logRun($rule, $subject, 'success', $message);
                        $summary['executed']++;
                    } catch (\Throwable $exception) {
                        $this->logRun($rule, $subject, 'failed', $exception->getMessage());
                        $summary['failed']++;
                    }
                }

                $rule->forceFill(['last_run_at' => now()])->saveQuietly();
            });

        return $summary;
    }

    private function subjectsFor(AutomationTrigger $trigger): Collection
    {
        return match ($trigger) {
            AutomationTrigger::TaskOverdue => Task::query()
                ->operational()
                ->overdue()
                ->with(['assignee', 'project.manager'])
                ->get(),
            AutomationTrigger::TaskDueSoon => Task::query()
                ->operational()
                ->open()
                ->whereBetween('due_date', [today(), today()->addDay()])
                ->with(['assignee', 'project.manager'])
                ->get(),
            AutomationTrigger::TicketSlaBreached => Ticket::query()
                ->whereNotIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<=', now())
                ->with('assignee')
                ->get(),
            AutomationTrigger::ProjectDueSoon => Project::query()
                ->operational()
                ->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value])
                ->whereBetween('due_date', [today(), today()->addDays(7)])
                ->with('manager')
                ->get(),
        };
    }

    private function matches(Model $subject, array $conditions): bool
    {
        foreach ($conditions as $field => $expected) {
            if ($expected === null || $expected === '') {
                continue;
            }

            $actual = $subject->getAttribute($field);

            if ($actual instanceof \BackedEnum) {
                $actual = $actual->value;
            }

            if ((string) $actual !== (string) $expected) {
                return false;
            }
        }

        return true;
    }

    private function alreadyRanToday(AutomationRule $rule, Model $subject): bool
    {
        return AutomationRun::query()
            ->where('automation_rule_id', $rule->id)
            ->where('subject_type', $subject::class)
            ->where('subject_id', $subject->getKey())
            ->whereDate('ran_at', today())
            ->exists();
    }

    private function execute(AutomationRule $rule, Model $subject): string
    {
        return match ($rule->action) {
            AutomationAction::NotifyAssignee => $this->notify(
                $this->assigneeFor($subject),
                $subject,
                $rule->name
            ),
            AutomationAction::NotifyManager => $this->notify(
                $this->managerFor($subject),
                $subject,
                $rule->name
            ),
            AutomationAction::NotifyUser => $this->notify(
                User::query()->find($rule->action_config['user_id'] ?? null),
                $subject,
                $rule->name
            ),
            AutomationAction::SetTicketPriority => $this->setTicketPriority($rule, $subject),
        };
    }

    private function notify(?User $recipient, Model $subject, string $ruleName): string
    {
        if (! $recipient) {
            return 'No recipient available.';
        }

        $route = FlowResourceRegistry::routeFor($subject);

        if (! $route) {
            return 'No route available for subject.';
        }

        $recipient->notify(new FlowNotification(
            kind: 'automation',
            titleKey: 'Automation alert',
            messageKey: ':rule requires your attention for :item.',
            parameters: [
                'rule' => $ruleName,
                'item' => FlowResourceRegistry::labelForModel($subject),
            ],
            routeName: $route,
            routeParameters: [$subject->getKey()],
            icon: 'bi-lightning-charge',
        ));

        return 'Notification sent to '.$recipient->email.'.';
    }

    private function setTicketPriority(AutomationRule $rule, Model $subject): string
    {
        if (! $subject instanceof Ticket) {
            return 'Action skipped: subject is not a ticket.';
        }

        $priority = TicketPriority::tryFrom((string) ($rule->action_config['priority'] ?? ''));

        if (! $priority) {
            return 'Action skipped: invalid priority.';
        }

        $subject->update(['priority' => $priority]);

        return 'Ticket priority updated to '.$priority->value.'.';
    }

    private function assigneeFor(Model $subject): ?User
    {
        return match (true) {
            $subject instanceof Task => $subject->assignee,
            $subject instanceof Ticket => $subject->assignee,
            $subject instanceof Project => $subject->manager,
            default => null,
        };
    }

    private function managerFor(Model $subject): ?User
    {
        return match (true) {
            $subject instanceof Project => $subject->manager,
            $subject instanceof Task => $subject->project?->manager,
            $subject instanceof Ticket => $subject->assignee,
            default => null,
        };
    }

    private function logRun(
        AutomationRule $rule,
        Model $subject,
        string $status,
        string $message
    ): void {
        AutomationRun::create([
            'automation_rule_id' => $rule->id,
            'status' => $status,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'message' => $message,
            'context' => [
                'label' => FlowResourceRegistry::labelForModel($subject),
            ],
            'ran_at' => now(),
        ]);
    }
}
