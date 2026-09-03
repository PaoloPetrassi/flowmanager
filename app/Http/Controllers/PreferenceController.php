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
                'activity' => __('Activity timeline'),
            ],
            'tableOptions' => [
                'projects' => [
                    'company' => __('Company'),
                    'status' => __('Status'),
                    'priority' => __('Priority'),
                    'manager' => __('Manager'),
                    'due_date' => __('Due date'),
                    'progress' => __('Progress'),
                    'tasks' => __('Tasks'),
                ],
                'tasks' => [
                    'project' => __('Project'),
                    'status' => __('Status'),
                    'priority' => __('Priority'),
                    'assignee' => __('Assignee'),
                    'due_date' => __('Due date'),
                ],
                'tickets' => [
                    'company' => __('Company / Contact'),
                    'status' => __('Status'),
                    'priority' => __('Priority'),
                    'assignee' => __('Assigned to'),
                    'sla' => __('SLA'),
                    'created' => __('Created'),
                ],
            ],
            'notificationOptions' => $this->notificationOptions(),
            'mailNotificationsEnabled' => (bool) config('flowmanager.notifications.mail_enabled'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $dashboardKeys = ['stats', 'my_work', 'projects', 'access', 'charts', 'activity'];
        $notificationKeys = array_keys($this->notificationOptions());

        $data = $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
            'density' => ['required', 'in:comfortable,compact'],
            'dashboard_widgets' => ['nullable', 'array'],
            'dashboard_widgets.*' => ['in:'.implode(',', $dashboardKeys)],
            'table_preferences' => ['nullable', 'array'],
            'table_preferences.*' => ['array'],
            'table_preferences.*.*' => ['string', 'max:40'],
            'notification_preferences' => ['nullable', 'array'],
            'notification_preferences.in_app' => ['nullable', 'array'],
            'notification_preferences.mail' => ['nullable', 'array'],
            'notification_preferences.in_app.*' => ['nullable', 'boolean'],
            'notification_preferences.mail.*' => ['nullable', 'boolean'],
        ]);

        $notificationPreferences = [
            'in_app' => [],
            'mail' => [],
        ];

        foreach ($notificationKeys as $key) {
            $notificationPreferences['in_app'][$key] = $request->boolean('notification_preferences.in_app.'.$key);
            $notificationPreferences['mail'][$key] = $request->boolean('notification_preferences.mail.'.$key);
        }

        auth()->user()->preference()->updateOrCreate([], [
            'theme' => $data['theme'],
            'density' => $data['density'],
            'dashboard_widgets' => array_values($data['dashboard_widgets'] ?? []),
            'table_preferences' => $data['table_preferences'] ?? [],
            'notification_preferences' => $notificationPreferences,
        ]);

        return back()->with('status', __('Preferences saved.'));
    }

    /**
     * @return array<string, string>
     */
    private function notificationOptions(): array
    {
        return [
            'assignments' => __('Assignments and reassignments'),
            'comments' => __('Comments'),
            'reminders' => __('Deadline and SLA reminders'),
            'automations' => __('Automation alerts'),
            'system' => __('System notifications'),
        ];
    }
}
