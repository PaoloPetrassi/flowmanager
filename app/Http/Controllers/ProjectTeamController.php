<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectTeamController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'role' => ['nullable', 'string', 'max:80'],
        ]);

        $project->teamMembers()->syncWithoutDetaching([
            $data['user_id'] => ['role' => trim((string) ($data['role'] ?? '')) ?: null],
        ]);

        $user = User::query()->find($data['user_id']);
        AuditService::record($project, 'team_member_added', [], ['user' => $user?->email]);

        return back()->with('status', __('Project team updated.'));
    }

    public function destroy(Project $project, User $user): RedirectResponse
    {
        $this->authorize('update', $project);

        if ($project->manager_id === $user->id) {
            return back()->with('error', __('The project manager cannot be removed from the team. Change the project manager first.'));
        }

        $project->teamMembers()->detach($user->id);
        AuditService::record($project, 'team_member_removed', ['user' => $user->email], []);

        return back()->with('status', __('Team member removed.'));
    }
}
