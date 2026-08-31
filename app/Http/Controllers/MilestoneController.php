<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->milestones()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', __('Milestone created.'));
    }

    public function toggle(Project $project, Milestone $milestone): RedirectResponse
    {
        $this->authorize('update', $project);
        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->update([
            'completed_at' => $milestone->completed_at ? null : now(),
        ]);

        return back()->with('status', __('Milestone updated.'));
    }

    public function destroy(Project $project, Milestone $milestone): RedirectResponse
    {
        $this->authorize('update', $project);
        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->delete();

        return back()->with('status', __('Milestone deleted.'));
    }
}
