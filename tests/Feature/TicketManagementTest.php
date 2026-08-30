<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTicketTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
    $user->roles()->attach($role);

    return $user;
}

function validTicketPayload(Company $company, array $overrides = []): array
{
    return array_merge([
        'company_id' => $company->id,
        'contact_id' => null,
        'assigned_to' => null,
        'reference' => 'TKT-2026-99999',
        'subject' => 'Unable to access service',
        'category' => TicketCategory::Access->value,
        'status' => TicketStatus::Open->value,
        'priority' => TicketPriority::High->value,
        'description' => 'Feature test support request.',
        'resolution' => null,
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('viewer can view tickets but cannot create them', function () {
    $viewer = createTicketTestUser('viewer');
    $company = Company::factory()->create(['created_by' => $viewer->id]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'assigned_to' => $viewer->id,
        'created_by' => $viewer->id,
    ]);

    $this->actingAs($viewer)->get(route('tickets.index'))->assertOk()->assertSee($ticket->reference);
    $this->actingAs($viewer)->post(route('tickets.store'), validTicketPayload($company))->assertForbidden();
});

test('operator can create and update a ticket', function () {
    $operator = createTicketTestUser('operator');
    $company = Company::factory()->create(['created_by' => $operator->id]);

    $response = $this->actingAs($operator)
        ->post(route('tickets.store'), validTicketPayload($company, ['assigned_to' => $operator->id]));

    $ticket = Ticket::query()->where('reference', 'TKT-2026-99999')->firstOrFail();
    $response->assertRedirect(route('tickets.show', $ticket));

    $this->actingAs($operator)
        ->put(route('tickets.update', $ticket), validTicketPayload($company, [
            'subject' => 'Access restored',
            'status' => TicketStatus::Resolved->value,
            'resolution' => 'Credentials were reset.',
        ]))
        ->assertRedirect(route('tickets.show', $ticket));

    $ticket->refresh();
    expect($ticket->resolved_at)->not->toBeNull();
    expect($ticket->subject)->toBe('Access restored');
});

test('ticket contact must belong to the selected company', function () {
    $operator = createTicketTestUser('operator');
    $company = Company::factory()->create(['created_by' => $operator->id]);
    $otherCompany = Company::factory()->create(['created_by' => $operator->id]);
    $contact = Contact::factory()->create([
        'company_id' => $otherCompany->id,
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->post(route('tickets.store'), validTicketPayload($company, [
            'contact_id' => $contact->id,
        ]))
        ->assertSessionHasErrors('contact_id');

    $this->assertDatabaseMissing('tickets', ['reference' => 'TKT-2026-99999']);
});

test('administrator can soft delete a ticket', function () {
    $administrator = createTicketTestUser('administrator');
    $company = Company::factory()->create(['created_by' => $administrator->id]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'assigned_to' => $administrator->id,
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->delete(route('tickets.destroy', $ticket))
        ->assertRedirect(route('tickets.index'));

    $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
});

test('operator can resolve and reopen a ticket with quick actions', function () {
    $operator = createTicketTestUser('operator');
    $company = Company::factory()->create(['created_by' => $operator->id]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => TicketStatus::InProgress,
        'resolved_at' => null,
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->patch(route('tickets.resolve', $ticket))
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Resolved);
    expect($ticket->resolved_at)->not->toBeNull();

    $this->actingAs($operator)
        ->patch(route('tickets.reopen', $ticket))
        ->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::InProgress);
    expect($ticket->resolved_at)->toBeNull();
});
