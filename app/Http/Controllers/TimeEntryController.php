<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimeEntryController extends Controller
{
    public function start(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $existing = TimeEntry::query()
            ->where('task_id', $task->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('ended_at')
            ->exists();

        if ($existing) {
            return back()->with('error', __('A timer is already running for this task.'));
        }

        TimeEntry::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'started_at' => now(),
            'minutes' => 0,
        ]);

        return back()->with('status', __('Timer started.'));
    }

    public function stop(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $entry = TimeEntry::query()
            ->where('task_id', $task->id)
            ->where('user_id', $request->user()->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $entry) {
            return back()->with('error', __('No running timer was found.'));
        }

        $endedAt = now();
        $entry->update([
            'ended_at' => $endedAt,
            'minutes' => max(1, $entry->started_at->diffInMinutes($endedAt)),
        ]);

        return back()->with('status', __('Timer stopped.'));
    }

    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $startedAt = Carbon::parse($data['date'])->setTime(9, 0);

        TimeEntry::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'started_at' => $startedAt,
            'ended_at' => $startedAt->copy()->addMinutes($data['minutes']),
            'minutes' => $data['minutes'],
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', __('Time entry added.'));
    }

    public function destroy(Request $request, Task $task, TimeEntry $timeEntry): RedirectResponse
    {
        $this->authorize('update', $task);
        abort_unless($timeEntry->task_id === $task->id, 404);
        abort_unless($timeEntry->user_id === $request->user()->id || $request->user()->hasRole('administrator'), 403);

        $timeEntry->delete();

        return back()->with('status', __('Time entry deleted.'));
    }
}
