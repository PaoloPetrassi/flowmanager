<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreferenceController extends Controller
{
    public function edit(): View
    {
        return view('preferences.edit', [
            'preferences' => auth()->user()->preference()->firstOrCreate([]),
            'dashboardOptions' => [
                'stats' => __('Summary cards'),
                'my_work' => __('My tasks and tickets'),
                'projects' => __('Managed projects'),
                'access' => __('Access summary'),
                'charts' => __('Charts and trends'),
            ],
            'tableOptions' => [
                'projects' => ['company' => __('Company'), 'status' => __('Status'), 'priority' => __('Priority'), 'manager' => __('Manager'), 'due_date' => __('Due date'), 'progress' => __('Progress'), 'tasks' => __('Tasks')],
                'tasks' => ['project' => __('Project'), 'status' => __('Status'), 'priority' => __('Priority'), 'assignee' => __('Assignee'), 'due_date' => __('Due date')],
                'tickets' => ['company' => __('Company / Contact'), 'status' => __('Status'), 'priority' => __('Priority'), 'assignee' => __('Assigned to'), 'sla' => __('SLA'), 'created' => __('Created')],
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
            'density' => ['required', 'in:comfortable,compact'],
            'dashboard_widgets' => ['nullable', 'array'],
            'dashboard_widgets.*' => ['in:stats,my_work,projects,access,charts'],
            'table_preferences' => ['nullable', 'array'],
            'table_preferences.*' => ['array'],
            'table_preferences.*.*' => ['string', 'max:40'],
        ]);

        auth()->user()->preference()->updateOrCreate([], [
            'theme' => $data['theme'],
            'density' => $data['density'],
            'dashboard_widgets' => array_values($data['dashboard_widgets'] ?? []),
            'table_preferences' => $data['table_preferences'] ?? [],
        ]);

        return back()->with('status', __('Preferences saved.'));
    }
}
