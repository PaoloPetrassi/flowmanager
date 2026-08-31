<?php

use App\Models\ApiToken;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function v013User(string $role = 'administrator'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::where('slug', $role)->firstOrFail());

    return $user;
}

test('administrator can create tags and custom fields', function () {
    $admin = v013User();

    $this->actingAs($admin)->post(route('tags.store'), ['name' => 'Strategic', 'color' => '#2563eb'])->assertRedirect();
    $this->actingAs($admin)->post(route('custom-fields.store'), [
        'resource_type' => 'company', 'name' => 'Customer code', 'field_type' => 'text',
    ])->assertRedirect();

    expect(Tag::where('slug', 'strategic')->exists())->toBeTrue()
        ->and(CustomField::where('slug', 'customer-code')->exists())->toBeTrue();
});

test('operators cannot administer extensibility settings', function () {
    $operator = v013User('operator');
    $this->actingAs($operator)->get(route('tags.index'))->assertForbidden();
    $this->actingAs($operator)->get(route('custom-fields.index'))->assertForbidden();
});

test('users can persist appearance preferences', function () {
    $user = v013User('viewer');
    $this->actingAs($user)->put(route('preferences.update'), ['theme' => 'dark', 'density' => 'compact'])->assertRedirect();
    expect($user->preference()->firstOrFail()->theme)->toBe('dark')
        ->and($user->preference()->firstOrFail()->density)->toBe('compact');
});

test('api tokens can read permitted resources', function () {
    $user = v013User('viewer');
    Company::factory()->create(['name' => 'API Company']);
    $plain = 'fm_'.Str::random(48);
    ApiToken::create(['user_id' => $user->id, 'name' => 'Test', 'token_hash' => hash('sha256', $plain), 'token_prefix' => substr($plain, 0, 12), 'abilities' => ['read']]);

    $this->withToken($plain)->getJson('/api/v1/companies')->assertOk()->assertJsonPath('data.0.name', 'API Company');
});

test('invalid api tokens are rejected', function () {
    $this->withToken('fm_invalid')->getJson('/api/v1/companies')->assertUnauthorized();
});

test('inbound api can create a support ticket', function () {
    $operator = v013User('operator');
    $plain = 'fm_'.Str::random(48);
    ApiToken::create(['user_id' => $operator->id, 'name' => 'Inbound', 'token_hash' => hash('sha256', $plain), 'token_prefix' => substr($plain, 0, 12), 'abilities' => ['write']]);

    $this->withToken($plain)->postJson('/api/v1/tickets/inbound', ['subject' => 'Inbound mail', 'body' => 'Message body', 'from_email' => 'sender@example.com'])->assertCreated();
    expect(Ticket::where('subject', 'Inbound mail')->exists())->toBeTrue();
});
