<?php

namespace App\Http\Controllers;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CollaborationService;
use App\Services\SavedFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request, SavedFilterService $savedFilters): View
    {
        Gate::authorize('viewAny', Ticket::class);

        $savedFilters->applyDefault($request, 'tickets');
        $perPage = $savedFilters->perPage($request);

        $search = trim((string) $request->query('search'));
        $companyId = (string) $request->query('company_id');
        $status = (string) $request->query('status');
        $priority = (string) $request->query('priority');
        $assigneeId = (string) $request->query('assigned_to');

        $allowedSorts = [
            'reference',
            'subject',
            'status',
            'priority',
            'created_at',
            'resolved_at',
        ];

        $sort = (string) $request->query('sort', 'created_at');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $direction = strtolower((string) $request->query('direction', 'desc'));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $tickets = Ticket::query()
            ->with([
                'company:id,name',
                'contact:id,first_name,last_name',
                'assignee:id,name',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('reference', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas(
                            'company',
                            fn (Builder $companyQuery) => $companyQuery
                                ->where('name', 'like', "%{$search}%")
                        )
                        ->orWhereHas(
                            'contact',
                            fn (Builder $contactQuery) => $contactQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                        );
                });
            })
            ->when(
                ctype_digit($companyId),
                fn (Builder $query) => $query->where('company_id', (int) $companyId)
            )
            ->when(
                TicketStatus::tryFrom($status) !== null,
                fn (Builder $query) => $query->where('status', $status)
            )
            ->when(
                TicketPriority::tryFrom($priority) !== null,
                fn (Builder $query) => $query->where('priority', $priority)
            )
            ->when(
                ctype_digit($assigneeId),
                fn (Builder $query) => $query->where('assigned_to', (int) $assigneeId)
            )
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'companies' => $this->companyOptions(),
            'users' => $this->userOptions(),
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'status' => $status,
                'priority' => $priority,
                'assigned_to' => $assigneeId,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Ticket::class);

        $companyId = (int) $request->query('company', 0);
        $contactId = (int) $request->query('contact', 0);

        $contact = Contact::query()
            ->with('company:id')
            ->find($contactId);

        if ($contact && ! $companyId) {
            $companyId = (int) ($contact->company_id ?? 0);
        }

        if ($contact && $companyId && (int) $contact->company_id !== $companyId) {
            $contact = null;
        }

        $companyId = Company::query()->whereKey($companyId)->exists()
            ? $companyId
            : null;

        return view('tickets.create', $this->formData(new Ticket([
            'company_id' => $companyId,
            'contact_id' => $contact?->id,
            'assigned_to' => $request->boolean('assign_to_me')
                ? Auth::id()
                : null,
            'reference' => $this->nextReference(),
            'category' => TicketCategory::General,
            'status' => TicketStatus::Open,
            'priority' => TicketPriority::Medium,
        ])));
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        Gate::authorize('create', Ticket::class);

        $ticket = Ticket::create([
            ...$this->normalizeResolution($request->validated()),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', __('Ticket created successfully.'));
    }

    public function show(Ticket $ticket): View
    {
        Gate::authorize('view', $ticket);

        $ticket->load([
            'company',
            'contact',
            'assignee',
            'creator',
        ]);

        return view('tickets.show', [
            'ticket' => $ticket,
            'collaboration' => CollaborationService::dataFor($ticket),
            'collaborationType' => 'ticket',
        ]);
    }

    public function edit(Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', $this->formData($ticket));
    }

    public function update(
        UpdateTicketRequest $request,
        Ticket $ticket
    ): RedirectResponse {
        Gate::authorize('update', $ticket);

        $ticket->update(
            $this->normalizeResolution($request->validated(), $ticket)
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', __('Ticket updated successfully.'));
    }

    public function resolve(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        $ticket->update([
            'status' => TicketStatus::Resolved,
            'resolved_at' => $ticket->resolved_at ?? now(),
        ]);

        return back()->with('status', __('Ticket marked as resolved.'));
    }

    public function reopen(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        $ticket->update([
            'status' => TicketStatus::InProgress,
            'resolved_at' => null,
        ]);

        return back()->with('status', __('Ticket reopened.'));
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('status', __('Ticket deleted successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Ticket $ticket): array
    {
        return [
            'ticket' => $ticket,
            'companies' => $this->companyOptions(),
            'contacts' => Contact::query()
                ->with('company:id,name')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'users' => $this->userOptions(),
            'categories' => TicketCategory::cases(),
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeResolution(array $data, ?Ticket $ticket = null): array
    {
        if (in_array($data['status'], [
            TicketStatus::Resolved->value,
            TicketStatus::Closed->value,
        ], true)) {
            $data['resolved_at'] = $ticket?->resolved_at ?? now();
        } else {
            $data['resolved_at'] = null;
        }

        return $data;
    }

    private function nextReference(): string
    {
        $nextId = (int) Ticket::withTrashed()->max('id') + 1;

        return sprintf('TKT-%s-%05d', now()->format('Y'), $nextId);
    }

    private function companyOptions()
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function userOptions()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
