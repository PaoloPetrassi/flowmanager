<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class GlobalSearchService
{
    /**
     * @return array<string, string>
     */
    public function scopes(Authenticatable $user): array
    {
        $scopes = [];

        foreach ($this->definitions() as $key => $definition) {
            if (Gate::forUser($user)->allows('viewAny', $definition['model'])) {
                $scopes[$key] = __($definition['label']);
            }
        }

        return $scopes;
    }

    /**
     * @return array<int, array{key:string,label:string,icon:string,items:array<int,array<string,mixed>>}>
     */
    public function groups(Authenticatable $user, string $search, ?string $scope = null, int $limit = 7): array
    {
        $groups = [];

        foreach ($this->definitions() as $key => $definition) {
            if ($scope && $scope !== $key) {
                continue;
            }

            if (! Gate::forUser($user)->allows('viewAny', $definition['model'])) {
                continue;
            }

            $items = $definition['search']($search, $limit);

            if ($items === []) {
                continue;
            }

            $groups[] = [
                'key' => $key,
                'label' => $definition['label'],
                'icon' => $definition['icon'],
                'items' => $items,
            ];
        }

        return $groups;
    }

    /**
     * @return array<int, array{label:string,type:string,url:string,icon:string,meta:string|null}>
     */
    public function palette(Authenticatable $user, string $search): array
    {
        return collect($this->groups($user, $search, null, 4))
            ->flatMap(function (array $group) {
                return collect($group['items'])->map(fn (array $item) => [
                    'label' => $item['title'],
                    'type' => __($group['label']),
                    'url' => $item['url'],
                    'icon' => $item['icon'] ?? $group['icon'],
                    'meta' => $item['subtitle'] ?: $item['meta'],
                ]);
            })
            ->take(18)
            ->values()
            ->all();
    }

    /**
     * @return array<string, array{model:class-string, label:string, icon:string, search:callable(string,int):array}>
     */
    private function definitions(): array
    {
        return [
            'companies' => [
                'model' => Company::class,
                'label' => 'Companies',
                'icon' => 'bi-buildings',
                'search' => fn (string $search, int $limit) => Company::query()
                    ->where(function (Builder $query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('legal_name', 'like', "%{$search}%")
                            ->orWhere('vat_number', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    })
                    ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$search.'%'])
                    ->orderBy('name')
                    ->limit($limit)
                    ->get()
                    ->map(fn (Company $company) => [
                        'title' => $company->name,
                        'subtitle' => collect([$company->legal_name, $company->city])->filter()->implode(' · '),
                        'meta' => $company->status->label(),
                        'url' => route('companies.show', $company),
                        'icon' => 'bi-buildings',
                    ])->all(),
            ],
            'contacts' => [
                'model' => Contact::class,
                'label' => 'Contacts',
                'icon' => 'bi-person-vcard',
                'search' => fn (string $search, int $limit) => Contact::query()
                    ->with('company:id,name')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('job_title', 'like', "%{$search}%")
                            ->orWhereHas('company', fn (Builder $companyQuery) => $companyQuery->where('name', 'like', "%{$search}%"));
                    })
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->limit($limit)
                    ->get()
                    ->map(fn (Contact $contact) => [
                        'title' => $contact->full_name,
                        'subtitle' => collect([$contact->company?->name, $contact->job_title])->filter()->implode(' · '),
                        'meta' => $contact->email,
                        'url' => route('contacts.show', $contact),
                        'icon' => 'bi-person-vcard',
                    ])->all(),
            ],
            'projects' => [
                'model' => Project::class,
                'label' => 'Projects',
                'icon' => 'bi-kanban',
                'search' => fn (string $search, int $limit) => Project::query()
                    ->operational()
                    ->with('company:id,name')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('company', fn (Builder $companyQuery) => $companyQuery->where('name', 'like', "%{$search}%"));
                    })
                    ->orderByRaw('CASE WHEN code LIKE ? OR name LIKE ? THEN 0 ELSE 1 END', [$search.'%', $search.'%'])
                    ->orderBy('name')
                    ->limit($limit)
                    ->get()
                    ->map(fn (Project $project) => [
                        'title' => $project->name,
                        'subtitle' => collect([$project->code, $project->company?->name])->filter()->implode(' · '),
                        'meta' => $project->status->label(),
                        'url' => route('projects.show', $project),
                        'icon' => 'bi-kanban',
                    ])->all(),
            ],
            'tasks' => [
                'model' => Task::class,
                'label' => 'Tasks',
                'icon' => 'bi-check2-square',
                'search' => fn (string $search, int $limit) => Task::query()
                    ->operational()
                    ->with('project:id,code,name')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%"));
                    })
                    ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', [$search.'%'])
                    ->orderBy('title')
                    ->limit($limit)
                    ->get()
                    ->map(fn (Task $task) => [
                        'title' => $task->title,
                        'subtitle' => collect([$task->project?->code, $task->project?->name])->filter()->implode(' · '),
                        'meta' => $task->status->label(),
                        'url' => route('tasks.show', $task),
                        'icon' => 'bi-check2-square',
                    ])->all(),
            ],
            'assets' => [
                'model' => Asset::class,
                'label' => 'Assets',
                'icon' => 'bi-laptop',
                'search' => fn (string $search, int $limit) => Asset::query()
                    ->where(function (Builder $query) use ($search) {
                        $query->where('asset_tag', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orderByRaw('CASE WHEN asset_tag LIKE ? OR name LIKE ? THEN 0 ELSE 1 END', [$search.'%', $search.'%'])
                    ->orderBy('name')
                    ->limit($limit)
                    ->get()
                    ->map(fn (Asset $asset) => [
                        'title' => $asset->name,
                        'subtitle' => collect([$asset->asset_tag, $asset->serial_number])->filter()->implode(' · '),
                        'meta' => $asset->status->label(),
                        'url' => route('assets.show', $asset),
                        'icon' => 'bi-laptop',
                    ])->all(),
            ],
            'tickets' => [
                'model' => Ticket::class,
                'label' => 'Tickets',
                'icon' => 'bi-ticket-perforated',
                'search' => fn (string $search, int $limit) => Ticket::query()
                    ->with('company:id,name')
                    ->where(function (Builder $query) use ($search) {
                        $query->where('reference', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('company', fn (Builder $companyQuery) => $companyQuery->where('name', 'like', "%{$search}%"));
                    })
                    ->orderByRaw('CASE WHEN reference LIKE ? OR subject LIKE ? THEN 0 ELSE 1 END', [$search.'%', $search.'%'])
                    ->orderByDesc('updated_at')
                    ->limit($limit)
                    ->get()
                    ->map(fn (Ticket $ticket) => [
                        'title' => $ticket->subject,
                        'subtitle' => collect([$ticket->reference, $ticket->company?->name])->filter()->implode(' · '),
                        'meta' => $ticket->status->label(),
                        'url' => route('tickets.show', $ticket),
                        'icon' => 'bi-ticket-perforated',
                    ])->all(),
            ],
            'users' => [
                'model' => User::class,
                'label' => 'Users',
                'icon' => 'bi-person',
                'search' => fn (string $search, int $limit) => User::query()
                    ->where(function (Builder $query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$search.'%'])
                    ->orderBy('name')
                    ->limit($limit)
                    ->get()
                    ->map(fn (User $user) => [
                        'title' => $user->name,
                        'subtitle' => $user->email,
                        'meta' => __('User'),
                        'url' => route('users.show', $user),
                        'icon' => 'bi-person',
                    ])->all(),
            ],
        ];
    }
}
