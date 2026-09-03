<?php

namespace App\Services;

use App\Enums\AutomationAction;
use App\Enums\AutomationTrigger;
use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
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
    public function run(?AutomationRule $onlyRule = null): array
    {
        $summary = ['rules' => 0, 'executed' => 0, 'skipped' => 0, 'failed' => 0];

        $rules = AutomationRule::query()
            ->where('is_active', true)
            ->when($onlyRule, fn ($query) => $query->whereKey($onlyRule->getKey()))
            ->orderBy('id')
            ->get();

        foreach ($rules as $rule) {
            $summary['rules']++;
            $this->executeRule($rule, $summary);
        }

        return $summary;
    }

    /**
     * @return array{matched:int, eligible:int, cooldown:int, items:array<int, string>}
     */
    public function preview(AutomationRule $rule): array
    {
        $matched = 0;
        $eligible = 0;
        $cooldown = 0;
        $items = [];

        foreach ($this->subjectsFor($rule->trigger) as $subject) {
            if (! $this->matches($subject, $rule->conditions ?? [])) {
                continue;
            }

            $matched++;

            if ($this->withinCooldown($rule, $subject)) {
                $cooldown++;
                continue;
            }

            $eligible++;

            if (count($items) < 5) {
                $items[] = FlowResourceRegistry::labelForModel($subject);
            }
        }

        return compact('matched', 'eligible', 'cooldown', 'items');
    }

    private function executeRule(AutomationRule $rule, array &$summary): void
    {
        foreach ($this->subjectsFor($rule->trigger) as $subject) {
            if (! $this->matches($subject, $rule->conditions ?? [])) {
                $summary['skipped']++;
                continue;
            }

            if ($this->withinCooldown($rule, $subject)) {
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
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [today(), today()->addDay()])
                ->with(['assignee', 'project.manager'])
                ->get(),
            AutomationTrigger::TaskUnassigned => Task::query()
                ->operational()
                ->open()
                ->whereNull('assigned_to')
                ->with(['assignee', 'project.manager'])
                ->get(),
            AutomationTrigger::TicketSlaBreached => Ticket::query()
                ->open()
                ->whereNotNull('sla_due_at')
                ->where('sla_due_at', '<=', now())
                ->with('assignee')
                ->get(),
            AutomationTrigger::TicketUnassigned => Ticket::query()
                ->open()
                ->whereNull('assigned_to')
                ->with('assignee')
                ->get(),
            AutomationTrigger::ProjectDueSoon => Project::query()
                ->operational()
                ->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value])
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [today(), today()->addDays(7)])
                ->with('manager')
                ->get(),
            AutomationTrigger::ProjectOverdue => Project::query()
                ->operational()
                ->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Cancelled->value])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
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

    private function withinCooldown(AutomationRule $rule, Model $subject): bool
    {
        $cooldownMinutes = max(15, (int) ($rule->cooldown_minutes ?: 1440));

        return AutomationRun::query()
            ->where('automation_rule_id', $rule->id)
            ->where('subject_type', $subject::class)
            ->where('subject_id', $subject->getKey())
            ->where('status', 'success')
            ->where('ran_at', '>=', now()->subMinutes($cooldownMinutes))
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
            AutomationAction::AssignUser => $this->assignUser($rule, $subject),
            AutomationAction::SetTicketPriority => $this->setTicketPriority($rule, $subject),
            AutomationAction::SetTaskPriority => $this->setTaskPriority($rule, $subject),
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

    private function assignUser(AutomationRule $rule, Model $subject): string
    {
        $user = User::query()->find($rule->action_config['user_id'] ?? null);

        if (! $user) {
            return 'Action skipped: target user not found.';
        }

        $attribute = match (true) {
            $subject instanceof Task, $subject instanceof Ticket => 'assigned_to',
            $subject instanceof Project => 'manager_id',
            default => null,
        };

        if (! $attribute) {
            return 'Action skipped: subject cannot be assigned.';
        }

        $subject->update([$attribute => $user->id]);

        return 'Assigned to '.$user->email.'.';
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

    private function setTaskPriority(AutomationRule $rule, Model $subject): string
    {
        if (! $subject instanceof Task) {
            return 'Action skipped: subject is not a task.';
        }

        $priority = TaskPriority::tryFrom((string) ($rule->action_config['priority'] ?? ''));

        if (! $priority) {
            return 'Action skipped: invalid priority.';
        }

        $subject->update(['priority' => $priority]);

        return 'Task priority updated to '.$priority->value.'.';
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
                'trigger' => $rule->trigger->value,
                'action' => $rule->action->value,
            ],
            'ran_at' => now(),
        ]);
    }
}
