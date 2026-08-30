<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type') === 'tickets'
            ? 'tickets'
            : 'tasks';

        $assigneeId = $request->integer('assigned_to') ?: null;

        if ($type === 'tickets') {
            Gate::authorize('viewAny', Ticket::class);

            $columns = collect(TicketStatus::cases())->mapWithKeys(
                fn (TicketStatus $status) => [$status->value => $status->label()]
            );

            $items = Ticket::query()
                ->with(['company:id,name', 'assignee:id,name'])
                ->when($assigneeId, fn ($query) => $query->where('assigned_to', $assigneeId))
                ->latest('updated_at')
                ->limit(250)
                ->get()
                ->groupBy(fn (Ticket $ticket) => $ticket->status->value);
        } else {
            Gate::authorize('viewAny', Task::class);

            $columns = collect(TaskStatus::cases())->mapWithKeys(
                fn (TaskStatus $status) => [$status->value => $status->label()]
            );

            $items = Task::query()
                ->with(['project:id,code,name', 'assignee:id,name'])
                ->when($assigneeId, fn ($query) => $query->where('assigned_to', $assigneeId))
                ->orderByRaw('due_date is null')
                ->orderBy('due_date')
                ->limit(250)
                ->get()
                ->groupBy(fn (Task $task) => $task->status->value);
        }

        return view('boards.index', [
            'type' => $type,
            'columns' => $columns,
            'items' => $items,
            'assigneeId' => $assigneeId,
            'canUpdate' => $type === 'tickets'
                ? $request->user()->hasPermission('tickets.update')
                : $request->user()->hasPermission('tasks.update'),
        ]);
    }

    public function updateTaskStatus(Request $request, Task $task): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ]);

        $status = TaskStatus::from($validated['status']);

        $task->update([
            'status' => $status->value,
            'completed_at' => $status === TaskStatus::Completed
                ? ($task->completed_at ?? now())
                : null,
        ]);

        return $this->statusResponse($request, __('Task status updated.'));
    }

    public function updateTicketStatus(Request $request, Ticket $ticket): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $ticket);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
        ]);

        $status = TicketStatus::from($validated['status']);

        $ticket->update([
            'status' => $status->value,
            'resolved_at' => in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true)
                ? ($ticket->resolved_at ?? now())
                : null,
        ]);

        return $this->statusResponse($request, __('Ticket status updated.'));
    }

    private function statusResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }
}
