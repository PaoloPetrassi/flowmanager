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

    public function run(Request $request, AutomationEngine $engine): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('automations.manage'), 403);

        $summary = $engine->run();

        return back()->with('status', __('Automations completed: :executed executed, :failed failed.', [
            'executed' => $summary['executed'],
            'failed' => $summary['failed'],
        ]));
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'trigger' => ['required', Rule::enum(AutomationTrigger::class)],
            'action' => ['required', Rule::enum(AutomationAction::class)],
            'condition_priority' => ['nullable', 'string', 'max:30'],
            'action_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'action_priority' => ['nullable', Rule::enum(TicketPriority::class)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $trigger = AutomationTrigger::from($validated['trigger']);
        $action = AutomationAction::from($validated['action']);

        if ($action === AutomationAction::NotifyUser && empty($validated['action_user_id'])) {
            throw ValidationException::withMessages([
                'action_user_id' => __('A target user is required for this automation action.'),
            ]);
        }

        if ($action === AutomationAction::SetTicketPriority && empty($validated['action_priority'])) {
            throw ValidationException::withMessages([
                'action_priority' => __('A target ticket priority is required for this automation action.'),
            ]);
        }

        if ($action === AutomationAction::SetTicketPriority && $trigger !== AutomationTrigger::TicketSlaBreached) {
            throw ValidationException::withMessages([
                'action' => __('Ticket priority can only be changed by a ticket SLA automation.'),
            ]);
        }

        if (! empty($validated['condition_priority']) && ! in_array($trigger, [
            AutomationTrigger::TaskOverdue,
            AutomationTrigger::TaskDueSoon,
            AutomationTrigger::TicketSlaBreached,
        ], true)) {
            throw ValidationException::withMessages([
                'condition_priority' => __('Priority conditions are not supported by the selected trigger.'),
            ]);
        }

        $conditions = array_filter([
            'priority' => $validated['condition_priority'] ?? null,
        ], fn ($value) => filled($value));

        $actionConfig = array_filter([
            'user_id' => $validated['action_user_id'] ?? null,
            'priority' => $validated['action_priority'] ?? null,
        ], fn ($value) => filled($value));

        return [
            'name' => $validated['name'],
            'trigger' => $validated['trigger'],
            'action' => $validated['action'],
            'conditions' => $conditions ?: null,
            'action_config' => $actionConfig ?: null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
