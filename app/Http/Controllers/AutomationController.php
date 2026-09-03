<?php

namespace App\Http\Controllers;

use App\Enums\AutomationAction;
use App\Enums\AutomationTrigger;
use App\Enums\TicketPriority;
use App\Models\AutomationRule;
use App\Models\User;
use App\Services\AutomationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AutomationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('automations.view'), 403);

        return view('automations.index', [
            'rules' => AutomationRule::query()
                ->with(['creator:id,name', 'runs' => fn ($query) => $query->latest('ran_at')->limit(5)])
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'triggers' => AutomationTrigger::cases(),
            'actions' => AutomationAction::cases(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'priorities' => TicketPriority::cases(),
            'cooldowns' => $this->cooldownOptions(),
            'preview' => session('automation_preview'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        $data = $this->validated($request);

        AutomationRule::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', __('Automation rule created.'));
    }

    public function update(Request $request, AutomationRule $automation): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        $automation->update($this->validated($request));

        return back()->with('status', __('Automation rule updated.'));
    }

    public function destroy(Request $request, AutomationRule $automation): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        $automation->delete();

        return back()->with('status', __('Automation rule deleted.'));
    }

    public function toggle(Request $request, AutomationRule $automation): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        $automation->update(['is_active' => ! $automation->is_active]);

        return back()->with('status', $automation->is_active
            ? __('Automation rule resumed.')
            : __('Automation rule paused.'));
    }

    public function run(Request $request, AutomationEngine $engine): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        $summary = $engine->run();

        return back()->with('status', $this->summaryMessage($summary));
    }

    public function runOne(Request $request, AutomationRule $automation, AutomationEngine $engine): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        if (! $automation->is_active) {
            return back()->withErrors([
                'automation' => __('Resume this automation rule before running it.'),
            ]);
        }

        $summary = $engine->run($automation);

        return back()->with('status', $this->summaryMessage($summary));
    }

    public function preview(Request $request, AutomationRule $automation, AutomationEngine $engine): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        return back()->with('automation_preview', [
            'rule_id' => $automation->id,
            'rule_name' => $automation->name,
            ...$engine->preview($automation),
        ]);
    }

    private function validated(Request $request): array
    {
        $priorityValues = array_column(TicketPriority::cases(), 'value');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'trigger' => ['required', Rule::enum(AutomationTrigger::class)],
            'action' => ['required', Rule::enum(AutomationAction::class)],
            'condition_priority' => ['nullable', Rule::in($priorityValues)],
            'action_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'action_priority' => ['nullable', Rule::in($priorityValues)],
            'cooldown_minutes' => ['required', 'integer', 'min:15', 'max:10080'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $trigger = AutomationTrigger::from($validated['trigger']);
        $action = AutomationAction::from($validated['action']);

        if (in_array($action, [AutomationAction::NotifyUser, AutomationAction::AssignUser], true)
            && empty($validated['action_user_id'])) {
            throw ValidationException::withMessages([
                'action_user_id' => __('A target user is required for this automation action.'),
            ]);
        }

        if (in_array($action, [AutomationAction::SetTicketPriority, AutomationAction::SetTaskPriority], true)
            && empty($validated['action_priority'])) {
            throw ValidationException::withMessages([
                'action_priority' => __('A target priority is required for this automation action.'),
            ]);
        }

        $ticketTriggers = [AutomationTrigger::TicketSlaBreached, AutomationTrigger::TicketUnassigned];
        $taskTriggers = [AutomationTrigger::TaskOverdue, AutomationTrigger::TaskDueSoon, AutomationTrigger::TaskUnassigned];

        if ($action === AutomationAction::SetTicketPriority && ! in_array($trigger, $ticketTriggers, true)) {
            throw ValidationException::withMessages([
                'action' => __('Ticket priority can only be changed by a ticket automation.'),
            ]);
        }

        if ($action === AutomationAction::SetTaskPriority && ! in_array($trigger, $taskTriggers, true)) {
            throw ValidationException::withMessages([
                'action' => __('Task priority can only be changed by a task automation.'),
            ]);
        }

        $conditions = array_filter([
            'priority' => $validated['condition_priority'] ?? null,
        ], fn ($value) => filled($value));

        $actionConfig = match ($action) {
            AutomationAction::NotifyUser, AutomationAction::AssignUser => [
                'user_id' => (int) $validated['action_user_id'],
            ],
            AutomationAction::SetTicketPriority, AutomationAction::SetTaskPriority => [
                'priority' => $validated['action_priority'],
            ],
            default => null,
        };

        return [
            'name' => $validated['name'],
            'trigger' => $validated['trigger'],
            'action' => $validated['action'],
            'conditions' => $conditions ?: null,
            'action_config' => $actionConfig,
            'cooldown_minutes' => (int) $validated['cooldown_minutes'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function summaryMessage(array $summary): string
    {
        return __('Automations completed: :executed executed, :failed failed, :skipped skipped.', [
            'executed' => $summary['executed'],
            'failed' => $summary['failed'],
            'skipped' => $summary['skipped'],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function cooldownOptions(): array
    {
        return [
            15 => __('15 minutes'),
            60 => __('1 hour'),
            360 => __('6 hours'),
            720 => __('12 hours'),
            1440 => __('1 day'),
            4320 => __('3 days'),
            10080 => __('7 days'),
        ];
    }
}
