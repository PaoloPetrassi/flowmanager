<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a user and assign the requested system role.
 */
function createContactTestUser(string $roleSlug): User
{
    $user = User::factory()->create();

    $role = Role::query()
        ->where('slug', $roleSlug)
        ->firstOrFail();

    $user->roles()->attach($role);

    return $user;
}

/**
 * Return a complete valid contact payload.
 *
 * @return array<string, mixed>
 */
function validContactPayload(
    Company $company,
    array $overrides = []
): array {
    return array_merge([
        'company_id' => $company->id,
        'first_name' => 'Mario',
        'last_name' => 'Rossi',
        'job_title' => 'Operations Manager',
        'department' => 'Operations',
        'email' => 'mario.rossi@example.test',
        'phone' => '+39 06 12345678',
        'mobile' => '+39 333 1234567',
        'is_primary' => '1',
        'notes' => 'Test contact.',
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest users cannot access the contacts module', function () {
    $response = $this->get(
        route('contacts.index')
    );

    $response->assertRedirect(
        route('login')
    );
});

test('viewer can view the contacts list', function () {
    $viewer = createContactTestUser('viewer');
    $company = Company::factory()->create([
        'created_by' => $viewer->id,
    ]);

    $contact = Contact::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Viewer',
        'last_name' => 'Contact',
        'created_by' => $viewer->id,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->get(route('contacts.index'));

    $response
        ->assertOk()
        ->assertSee('Contacts')
        ->assertSee($contact->full_name);
});

test('viewer cannot create a contact', function () {
    $viewer = createContactTestUser('viewer');
    $company = Company::factory()->create([
        'created_by' => $viewer->id,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->post(
            route('contacts.store'),
            validContactPayload($company)
        );

    $response->assertForbidden();

    $this->assertDatabaseMissing('contacts', [
        'email' => 'mario.rossi@example.test',
    ]);
});

test('operator can create a contact', function () {
    $operator = createContactTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $response = $this
        ->actingAs($operator)
        ->post(
            route('contacts.store'),
            validContactPayload($company)
        );

    $contact = Contact::query()
        ->where('email', 'mario.rossi@example.test')
        ->firstOrFail();

    $response->assertRedirect(
        route('contacts.show', $contact)
    );

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'company_id' => $company->id,
        'first_name' => 'Mario',
        'last_name' => 'Rossi',
        'is_primary' => true,
        'created_by' => $operator->id,
    ]);
});

test('operator can update a contact', function () {
    $operator = createContactTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $contact = Contact::factory()->create([
        'company_id' => $company->id,
        'first_name' => 'Old',
        'last_name' => 'Name',
        'created_by' => $operator->id,
    ]);

    $response = $this
        ->actingAs($operator)
        ->put(
            route('contacts.update', $contact),
            validContactPayload($company, [
                'first_name' => 'Updated',
                'last_name' => 'Contact',
                'is_primary' => '0',
            ])
        );

    $response->assertRedirect(
        route('contacts.show', $contact)
    );

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'first_name' => 'Updated',
        'last_name' => 'Contact',
        'is_primary' => false,
    ]);
});

test('administrator can soft delete a contact', function () {
    $administrator = createContactTestUser(
        'administrator'
    );

    $company = Company::factory()->create([
        'created_by' => $administrator->id,
    ]);

    $contact = Contact::factory()->create([
        'company_id' => $company->id,
        'created_by' => $administrator->id,
    ]);

    $response = $this
        ->actingAs($administrator)
        ->delete(
            route('contacts.destroy', $contact)
        );

    $response->assertRedirect(
        route('contacts.index')
    );

    $this->assertSoftDeleted('contacts', [
        'id' => $contact->id,
    ]);
});

test('setting a primary contact replaces the previous primary contact', function () {
    $operator = createContactTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $previousPrimary = Contact::factory()->create([
        'company_id' => $company->id,
        'is_primary' => true,
        'created_by' => $operator->id,
    ]);

    $response = $this
        ->actingAs($operator)
        ->post(
            route('contacts.store'),
            validContactPayload($company)
        );

    $response->assertRedirect();

    expect(
        $previousPrimary->fresh()->is_primary
    )->toBeFalse();

    $this->assertDatabaseHas('contacts', [
        'email' => 'mario.rossi@example.test',
        'company_id' => $company->id,
        'is_primary' => true,
    ]);
});

test('standalone contacts cannot be marked as primary', function () {
    $operator = createContactTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $response = $this
        ->actingAs($operator)
        ->post(
            route('contacts.store'),
            validContactPayload($company, [
                'company_id' => null,
                'is_primary' => '1',
            ])
        );

    $response->assertRedirect();

    $this->assertDatabaseHas('contacts', [
        'email' => 'mario.rossi@example.test',
        'company_id' => null,
        'is_primary' => false,
    ]);
});

test('contacts can be searched by name and company', function () {
    $viewer = createContactTestUser('viewer');

    $targetCompany = Company::factory()->create([
        'name' => 'Searchable Industries',
        'created_by' => $viewer->id,
    ]);

    $otherCompany = Company::factory()->create([
        'name' => 'Different Company',
        'created_by' => $viewer->id,
    ]);

    Contact::factory()->create([
        'company_id' => $targetCompany->id,
        'first_name' => 'Alice',
        'last_name' => 'Target',
        'created_by' => $viewer->id,
    ]);

    Contact::factory()->create([
        'company_id' => $otherCompany->id,
        'first_name' => 'Bob',
        'last_name' => 'Unrelated',
        'created_by' => $viewer->id,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->get(
            route('contacts.index', [
                'search' => 'Searchable Industries',
            ])
        );

    $response
        ->assertOk()
        ->assertSee('Alice Target')
        ->assertDontSee('Bob Unrelated');
});

test('contacts can be filtered by company and primary status', function () {
    $viewer = createContactTestUser('viewer');

    $targetCompany = Company::factory()->create([
        'created_by' => $viewer->id,
    ]);

    $otherCompany = Company::factory()->create([
        'created_by' => $viewer->id,
    ]);

    Contact::factory()->create([
        'company_id' => $targetCompany->id,
        'first_name' => 'Primary',
        'last_name' => 'Target',
        'is_primary' => true,
        'created_by' => $viewer->id,
    ]);

    Contact::factory()->create([
        'company_id' => $targetCompany->id,
        'first_name' => 'Standard',
        'last_name' => 'Target',
        'is_primary' => false,
        'created_by' => $viewer->id,
    ]);

    Contact::factory()->create([
        'company_id' => $otherCompany->id,
        'first_name' => 'Primary',
        'last_name' => 'Other',
        'is_primary' => true,
        'created_by' => $viewer->id,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->get(
            route('contacts.index', [
                'company_id' => $targetCompany->id,
                'primary' => '1',
            ])
        );

    $response
        ->assertOk()
        ->assertSee('Primary Target')
        ->assertDontSee('Standard Target')
        ->assertDontSee('Primary Other');
});

test('contacts must reference an existing active company', function () {
    $operator = createContactTestUser('operator');
    $company = Company::factory()->create([
        'created_by' => $operator->id,
    ]);

    $response = $this
        ->actingAs($operator)
        ->from(route('contacts.create'))
        ->post(
            route('contacts.store'),
            validContactPayload($company, [
                'company_id' => 999999,
            ])
        );

    $response
        ->assertRedirect(
            route('contacts.create')
        )
        ->assertSessionHasErrors([
            'company_id',
        ]);
});
