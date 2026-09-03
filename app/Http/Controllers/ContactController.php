<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Ticket;
use App\Services\CollaborationService;
use App\Services\SavedFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display a listing of the contacts.
     */
    public function index(Request $request, SavedFilterService $savedFilters): View
    {
        Gate::authorize('viewAny', Contact::class);

        $savedFilters->applyDefault($request, 'contacts');
        $perPage = $savedFilters->perPage($request);

        $search = trim((string) $request->query('search'));
        $companyId = (string) $request->query('company_id');
        $primary = (string) $request->query('primary');

        $allowedSorts = [
            'last_name',
            'first_name',
            'job_title',
            'created_at',
        ];

        $sort = (string) $request->query(
            'sort',
            'last_name'
        );

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'last_name';
        }

        $direction = strtolower(
            (string) $request->query('direction', 'asc')
        );

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $contacts = Contact::query()
            ->with([
                'company',
                'creator',
            ])

            ->when(
                $search !== '',
                function (Builder $query) use ($search) {
                    $query->where(function (Builder $query) use ($search) {
                        $query
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('job_title', 'like', "%{$search}%")
                            ->orWhere('department', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhereHas(
                                'company',
                                fn (Builder $companyQuery) => $companyQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('legal_name', 'like', "%{$search}%")
                            );
                    });
                }
            )

            ->when(
                ctype_digit($companyId),
                fn (Builder $query) => $query->where(
                    'company_id',
                    (int) $companyId
                )
            )

            ->when(
                in_array($primary, ['0', '1'], true),
                fn (Builder $query) => $query->where(
                    'is_primary',
                    $primary === '1'
                )
            )

            ->orderBy($sort, $direction)
            ->orderBy('first_name', $direction)

            ->paginate($perPage)
            ->withQueryString();

        $companies = Company::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return view('contacts.index', [
            'contacts' => $contacts,
            'companies' => $companies,

            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'primary' => $primary,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new contact.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Contact::class);

        $selectedCompanyId = (int) $request->query(
            'company',
            0
        );

        if (! Company::query()
            ->whereKey($selectedCompanyId)
            ->exists()) {
            $selectedCompanyId = 0;
        }

        return view('contacts.create', [
            'contact' => new Contact([
                'company_id' => $selectedCompanyId ?: null,
            ]),
            'companies' => $this->companyOptions(),
        ]);
    }

    /**
     * Store a newly created contact.
     */
    public function store(
        StoreContactRequest $request
    ): RedirectResponse {
        Gate::authorize('create', Contact::class);

        $contact = DB::transaction(function () use ($request) {
            $data = $this->normalizedContactData(
                $request->validated()
            );

            if ($data['is_primary']) {
                $this->clearExistingPrimaryContact(
                    (int) $data['company_id']
                );
            }

            return Contact::create([
                ...$data,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('contacts.show', $contact)
            ->with(
                'status',
                __('Contact created successfully.')
            );
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact): View
    {
        Gate::authorize('view', $contact);

        $contact->load([
            'company',
            'creator',
        ]);

        $projects = collect();
        $tickets = collect();
        $relatedCounts = [
            'projects' => 0,
            'tickets' => 0,
        ];

        if (Gate::allows('viewAny', Project::class)) {
            $relatedCounts['projects'] = $contact->projects()->count();
            $projects = $contact->projects()
                ->with(['company:id,name', 'manager:id,name'])
                ->withCount('tasks')
                ->latest('updated_at')
                ->limit(6)
                ->get();
        }

        if (Gate::allows('viewAny', Ticket::class)) {
            $relatedCounts['tickets'] = $contact->tickets()->count();
            $tickets = $contact->tickets()
                ->with(['company:id,name', 'assignee:id,name'])
                ->latest('updated_at')
                ->limit(6)
                ->get();
        }

        return view('contacts.show', [
            'contact' => $contact,
            'projects' => $projects,
            'tickets' => $tickets,
            'relatedCounts' => $relatedCounts,
            'collaboration' => CollaborationService::dataFor($contact),
            'collaborationType' => 'contact',
        ]);
    }

    /**
     * Show the form for editing the specified contact.
     */
    public function edit(Contact $contact): View
    {
        Gate::authorize('update', $contact);

        return view('contacts.edit', [
            'contact' => $contact,
            'companies' => $this->companyOptions(),
        ]);
    }

    /**
     * Update the specified contact.
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): RedirectResponse {
        Gate::authorize('update', $contact);

        DB::transaction(function () use ($request, $contact) {
            $data = $this->normalizedContactData(
                $request->validated()
            );

            if ($data['is_primary']) {
                $this->clearExistingPrimaryContact(
                    (int) $data['company_id'],
                    $contact->id
                );
            }

            $contact->update($data);
        });

        return redirect()
            ->route('contacts.show', $contact)
            ->with(
                'status',
                __('Contact updated successfully.')
            );
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        Gate::authorize('delete', $contact);

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with(
                'status',
                __('Contact deleted successfully.')
            );
    }

    /**
     * Return the companies available for contact assignment.
     */
    private function companyOptions()
    {
        return Company::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);
    }

    /**
     * Normalize contact data before persistence.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedContactData(array $data): array
    {
        $rawCompanyId = $data['company_id'] ?? null;

        $companyId = $rawCompanyId !== null
            && $rawCompanyId !== ''
                ? (int) $rawCompanyId
                : null;

        return [
            ...$data,
            'company_id' => $companyId,
            'is_primary' => $companyId !== null
                && (bool) ($data['is_primary'] ?? false),
        ];
    }

    /**
     * Ensure only one primary contact exists per company.
     */
    private function clearExistingPrimaryContact(
        int $companyId,
        ?int $exceptContactId = null
    ): void {
        Contact::query()
            ->where('company_id', $companyId)
            ->where('is_primary', true)
            ->when(
                $exceptContactId !== null,
                fn (Builder $query) => $query->whereKeyNot(
                    $exceptContactId
                )
            )
            ->update([
                'is_primary' => false,
            ]);
    }
}
