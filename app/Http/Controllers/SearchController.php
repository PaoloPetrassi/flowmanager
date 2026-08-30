<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q'));
        $groups = [];

        if (mb_strlen($query) >= 2) {
            $groups = $this->search($query);
        }

        return view('search.index', [
            'query' => $query,
            'groups' => $groups,
            'resultCount' => collect($groups)->sum(
                fn (array $group) => count($group['items'])
            ),
        ]);
    }

    private function search(string $search): array
    {
        $groups = [];

        if (Gate::allows('viewAny', Company::class)) {
            $items = Company::query()
                ->where(function (Builder $query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('legal_name', 'like', "%{$search}%")
                        ->orWhere('vat_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(7)
                ->get()
                ->map(fn (Company $company) => [
                    'title' => $company->name,
                    'subtitle' => collect([$company->legal_name, $company->city])->filter()->implode(' · '),
                    'meta' => $company->status->label(),
                    'url' => route('companies.show', $company),
                    'icon' => 'bi-buildings',
                ])->all();

            $this->pushGroup($groups, 'Companies', $items);
        }

        if (Gate::allows('viewAny', Contact::class)) {
            $items = Contact::query()
                ->with('company:id,name')
                ->where(function (Builder $query) use ($search) {
                    $query
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%")
                        ->orWhereHas('company', fn (Builder $companyQuery) => $companyQuery
                            ->where('name', 'like', "%{$search}%"));
                })
                ->orderBy('last_name')
                ->limit(7)
                ->get()
                ->map(fn (Contact $contact) => [
                    'title' => $contact->full_name,
                    'subtitle' => collect([$contact->company?->name, $contact->job_title])->filter()->implode(' · '),
                    'meta' => $contact->email,
                    'url' => route('contacts.show', $contact),
                    'icon' => 'bi-person-vcard',
                ])->all();

            $this->pushGroup($groups, 'Contacts', $items);
        }

        if (Gate::allows('viewAny', Project::class)) {
            $items = Project::query()
                ->with('company:id,name')
                ->where(function (Builder $query) use ($search) {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('company', fn (Builder $companyQuery) => $companyQuery
                            ->where('name', 'like', "%{$search}%"));
                })
                ->orderBy('name')
                ->limit(7)
                ->get()
                ->map(fn (Project $project) => [
                    'title' => $project->name,
                    'subtitle' => $project->code.' · '.$project->company->name,
                    'meta' => $project->status->label(),
                    'url' => route('projects.show', $project),
                    'icon' => 'bi-kanban',
                ])->all();

            $this->pushGroup($groups, 'Projects', $items);
        }

        if (Gate::allows('viewAny', Task::class)) {
            $items = Task::query()
                ->with('project:id,code,name')
                ->where(function (Builder $query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('project', fn (Builder $projectQuery) => $projectQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                })
                ->orderBy('title')
                ->limit(7)
                ->get()
                ->map(fn (Task $task) => [
                    'title' => $task->title,
                    'subtitle' => $task->project->code.' · '.$task->project->name,
                    'meta' => $task->status->label(),
                    'url' => route('tasks.show', $task),
                    'icon' => 'bi-check2-square',
                ])->all();

            $this->pushGroup($groups, 'Tasks', $items);
        }

        if (Gate::allows('viewAny', Asset::class)) {
            $items = Asset::query()
                ->where(function (Builder $query) use ($search) {
                    $query
                        ->where('asset_tag', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(7)
                ->get()
                ->map(fn (Asset $asset) => [
                    'title' => $asset->name,
                    'subtitle' => collect([$asset->asset_tag, $asset->serial_number])->filter()->implode(' · '),
                    'meta' => $asset->status->label(),
                    'url' => route('assets.show', $asset),
                    'icon' => 'bi-laptop',
                ])->all();

            $this->pushGroup($groups, 'Assets', $items);
        }

        if (Gate::allows('viewAny', Ticket::class)) {
            $items = Ticket::query()
                ->with('company:id,name')
                ->where(function (Builder $query) use ($search) {
                    $query
                        ->where('reference', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('company', fn (Builder $companyQuery) => $companyQuery
                            ->where('name', 'like', "%{$search}%"));
                })
                ->orderByDesc('updated_at')
                ->limit(7)
                ->get()
                ->map(fn (Ticket $ticket) => [
                    'title' => $ticket->subject,
                    'subtitle' => collect([$ticket->reference, $ticket->company?->name])->filter()->implode(' · '),
                    'meta' => $ticket->status->label(),
                    'url' => route('tickets.show', $ticket),
                    'icon' => 'bi-ticket-perforated',
                ])->all();

            $this->pushGroup($groups, 'Tickets', $items);
        }

        if (Gate::allows('viewAny', User::class)) {
            $items = User::query()
                ->where(function (Builder $query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(7)
                ->get()
                ->map(fn (User $user) => [
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'meta' => __('User'),
                    'url' => route('users.show', $user),
                    'icon' => 'bi-person',
                ])->all();

            $this->pushGroup($groups, 'Users', $items);
        }

        return $groups;
    }

    private function pushGroup(
        array &$groups,
        string $label,
        array $items
    ): void {
        if ($items !== []) {
            $groups[] = [
                'label' => $label,
                'items' => $items,
            ];
        }
    }
}
