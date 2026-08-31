<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\DocumentTemplate;
use App\Models\ImportRun;
use App\Models\Project;
use App\Models\SavedFilter;
use App\Models\SavedReport;
use App\Models\ScheduledReport;
use App\Models\Tag;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class V013DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $user = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'administrator'))
            ->orderBy('id')
            ->first()
            ?? User::query()->orderBy('id')->first();

        if (! $user) {
            return;
        }

        Model::withoutEvents(function () use ($user): void {
            $tags = $this->seedTags();
            $fields = $this->seedCustomFields();

            $this->attachDemoTags($tags);
            $this->seedCustomFieldValues($fields);
            $this->seedSavedFilters($user);
            $this->seedPreferences($user);
            $this->seedIntegrationExamples($user);
            $this->seedDocumentTemplates($user);
            $this->seedAnalyticsExamples($user);
        });
    }

    private function seedTags(): array
    {
        $definitions = [
            ['name' => 'Strategic', 'color' => '#2563eb'],
            ['name' => 'VIP', 'color' => '#7c3aed'],
            ['name' => 'Renewal', 'color' => '#0891b2'],
            ['name' => 'Internal', 'color' => '#64748b'],
            ['name' => 'Urgent', 'color' => '#dc2626'],
        ];

        $tags = [];

        foreach ($definitions as $definition) {
            $slug = Str::slug($definition['name']);

            $tags[$slug] = Tag::query()->updateOrCreate(
                ['slug' => $slug],
                $definition + ['slug' => $slug]
            );
        }

        return $tags;
    }

    private function seedCustomFields(): array
    {
        $definitions = [
            'company.customer-tier' => [
                'resource_type' => 'company',
                'name' => 'Customer tier',
                'slug' => 'customer-tier',
                'field_type' => 'select',
                'options' => ['Standard', 'Premium', 'Enterprise'],
                'is_required' => false,
                'sort_order' => 10,
            ],
            'contact.preferred-channel' => [
                'resource_type' => 'contact',
                'name' => 'Preferred channel',
                'slug' => 'preferred-channel',
                'field_type' => 'select',
                'options' => ['Email', 'Phone', 'Teams'],
                'is_required' => false,
                'sort_order' => 10,
            ],
            'project.environment' => [
                'resource_type' => 'project',
                'name' => 'Environment',
                'slug' => 'environment',
                'field_type' => 'select',
                'options' => ['Production', 'Test', 'Development'],
                'is_required' => false,
                'sort_order' => 10,
            ],
            'task.acceptance-criteria' => [
                'resource_type' => 'task',
                'name' => 'Acceptance criteria',
                'slug' => 'acceptance-criteria',
                'field_type' => 'textarea',
                'options' => null,
                'is_required' => false,
                'sort_order' => 10,
            ],
            'asset.cost-center' => [
                'resource_type' => 'asset',
                'name' => 'Cost center',
                'slug' => 'cost-center',
                'field_type' => 'text',
                'options' => null,
                'is_required' => false,
                'sort_order' => 10,
            ],
            'ticket.business-impact' => [
                'resource_type' => 'ticket',
                'name' => 'Business impact',
                'slug' => 'business-impact',
                'field_type' => 'select',
                'options' => ['Low', 'Medium', 'High'],
                'is_required' => false,
                'sort_order' => 10,
            ],
        ];

        $fields = [];

        foreach ($definitions as $key => $definition) {
            $fields[$key] = CustomField::query()->updateOrCreate(
                [
                    'resource_type' => $definition['resource_type'],
                    'slug' => $definition['slug'],
                ],
                $definition + ['is_active' => true]
            );
        }

        return $fields;
    }

    private function attachDemoTags(array $tags): void
    {
        Company::query()->orderBy('id')->limit(4)->get()->each(function (Company $company, int $index) use ($tags): void {
            $tagIds = match ($index) {
                0 => [$tags['strategic']->id, $tags['vip']->id],
                1 => [$tags['renewal']->id],
                default => [$tags['strategic']->id],
            };

            $company->tags()->syncWithoutDetaching($tagIds);
        });

        Project::query()->operational()->orderBy('id')->limit(4)->get()->each(function (Project $project, int $index) use ($tags): void {
            $project->tags()->syncWithoutDetaching([
                $index === 0 ? $tags['strategic']->id : $tags['internal']->id,
            ]);
        });

        Task::query()->orderBy('id')->limit(4)->get()->each(function (Task $task, int $index) use ($tags): void {
            $task->tags()->syncWithoutDetaching([
                $index < 2 ? $tags['urgent']->id : $tags['internal']->id,
            ]);
        });

        Ticket::query()->orderBy('id')->limit(4)->get()->each(function (Ticket $ticket, int $index) use ($tags): void {
            $ticket->tags()->syncWithoutDetaching([
                $index < 2 ? $tags['urgent']->id : $tags['vip']->id,
            ]);
        });
    }

    private function seedCustomFieldValues(array $fields): void
    {
        $this->setValue(
            Company::query()->orderBy('id')->first(),
            $fields['company.customer-tier'],
            'Enterprise'
        );

        $this->setValue(
            Contact::query()->orderBy('id')->first(),
            $fields['contact.preferred-channel'],
            'Email'
        );

        $this->setValue(
            Project::query()->operational()->orderBy('id')->first(),
            $fields['project.environment'],
            'Production'
        );

        $this->setValue(
            Task::query()->orderBy('id')->first(),
            $fields['task.acceptance-criteria'],
            'Validated by the assignee and confirmed by the project manager.'
        );

        $this->setValue(
            Asset::query()->orderBy('id')->first(),
            $fields['asset.cost-center'],
            'IT-OPS-001'
        );

        $this->setValue(
            Ticket::query()->orderBy('id')->first(),
            $fields['ticket.business-impact'],
            'High'
        );
    }

    private function setValue(?Model $model, CustomField $field, string $value): void
    {
        if (! $model || ! method_exists($model, 'customFieldValues')) {
            return;
        }

        $model->customFieldValues()->updateOrCreate(
            ['custom_field_id' => $field->id],
            ['value' => $value]
        );
    }

    private function seedSavedFilters(User $user): void
    {
        $filters = [
            ['resource_type' => 'companies', 'name' => 'Active customers', 'filters' => ['type' => 'customer', 'status' => 'active']],
            ['resource_type' => 'projects', 'name' => 'Active projects', 'filters' => ['status' => 'active']],
            ['resource_type' => 'tasks', 'name' => 'High priority tasks', 'filters' => ['priority' => 'high']],
            ['resource_type' => 'tickets', 'name' => 'Urgent tickets', 'filters' => ['priority' => 'urgent']],
        ];

        foreach ($filters as $filter) {
            SavedFilter::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'resource_type' => $filter['resource_type'],
                    'name' => $filter['name'],
                ],
                [
                    'filters' => $filter['filters'],
                    'is_default' => false,
                ]
            );
        }
    }

    private function seedPreferences(User $user): void
    {
        UserPreference::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => 'system',
                'density' => 'comfortable',
                'dashboard_widgets' => ['stats', 'my_work', 'projects', 'access', 'charts'],
                'table_preferences' => [],
            ]
        );
    }

    private function seedIntegrationExamples(User $user): void
    {
        ImportRun::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'original_filename' => 'demo-companies.csv',
            ],
            [
                'resource_type' => 'companies',
                'total_rows' => 12,
                'imported_rows' => 11,
                'failed_rows' => 1,
                'errors' => [
                    ['row' => 7, 'message' => 'Duplicate VAT number in demo import.'],
                ],
            ]
        );

        $webhook = Webhook::query()->updateOrCreate(
            ['name' => 'Demo integration webhook'],
            [
                'url' => 'https://example.invalid/flowmanager-webhook',
                'secret' => 'demo-secret-not-for-production',
                'events' => ['created', 'updated', 'ticket.resolved'],
                'is_active' => false,
                'created_by' => $user->id,
            ]
        );

        WebhookDelivery::query()->updateOrCreate(
            [
                'webhook_id' => $webhook->id,
                'event' => 'created',
                'response_body' => 'Demo delivery generated by V013DemoSeeder.',
            ],
            [
                'payload' => [
                    'event' => 'created',
                    'resource' => 'Ticket',
                    'id' => 1,
                    'data' => ['demo' => true],
                ],
                'status_code' => 200,
                'successful' => true,
                'delivered_at' => now()->subDay(),
            ]
        );
    }

    private function seedDocumentTemplates(User $user): void
    {
        $templates = [
            [
                'name' => 'Company profile',
                'resource_type' => 'company',
                'default_format' => 'pdf',
                'content' => "Company profile\n\nName: {{name}}\nEmail: {{email}}\nPhone: {{phone}}\nWebsite: {{website}}",
            ],
            [
                'name' => 'Project status summary',
                'resource_type' => 'project',
                'default_format' => 'pdf',
                'content' => "Project status summary\n\nProject: {{name}}\nCode: {{code}}\nCompany: {{company.name}}\nManager: {{manager.name}}\nStatus: {{status}}\nDue date: {{due_date}}",
            ],
            [
                'name' => 'Ticket resolution sheet',
                'resource_type' => 'ticket',
                'default_format' => 'doc',
                'content' => "Ticket resolution sheet\n\nTicket: {{reference}}\nSubject: {{subject}}\nCompany: {{company.name}}\nAssigned to: {{assignee.name}}\nStatus: {{status}}\nResolution: {{resolution}}",
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::query()->updateOrCreate(
                [
                    'name' => $template['name'],
                    'resource_type' => $template['resource_type'],
                ],
                $template + [
                    'is_active' => true,
                    'created_by' => $user->id,
                ]
            );
        }
    }

    private function seedAnalyticsExamples(User $user): void
    {
        $reports = [
            ['name' => 'Project portfolio by status', 'dataset' => 'projects', 'chart_type' => 'bar', 'filters' => [], 'metrics' => ['count']],
            ['name' => 'Task throughput', 'dataset' => 'tasks', 'chart_type' => 'line', 'filters' => [], 'metrics' => ['completed']],
            ['name' => 'Open tickets by priority', 'dataset' => 'tickets', 'chart_type' => 'pie', 'filters' => ['open_only' => true], 'metrics' => ['count']],
        ];

        foreach ($reports as $report) {
            SavedReport::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $report['name'],
                ],
                $report + ['is_shared' => true]
            );
        }

        ScheduledReport::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Weekly project portfolio demo',
            ],
            [
                'report_type' => 'projects',
                'frequency' => 'weekly',
                'email' => $user->email,
                'is_active' => false,
                'last_sent_at' => null,
                'next_run_at' => now()->addWeek()->startOfDay()->addHours(8),
                'last_error' => null,
            ]
        );
    }
}
