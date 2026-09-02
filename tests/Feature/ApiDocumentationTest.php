<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function apiDocsUser(string $role = 'administrator'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $role)->firstOrFail());

    return $user;
}

test('openapi specification is available as json', function () {
    $this->getJson('/api/openapi.json')
        ->assertOk()
        ->assertJsonPath('openapi', '3.1.0')
        ->assertJsonPath('info.version', config('flowmanager.version'))
        ->assertJsonStructure([
            'paths' => [
                '/companies' => ['get'],
                '/tickets/inbound' => ['post'],
            ],
            'webhooks' => ['flowManagerEvent'],
            'components' => ['securitySchemes', 'schemas'],
        ]);
});

test('administrator can view api documentation page', function () {
    $administrator = apiDocsUser();

    $this->actingAs($administrator)
        ->get(route('integrations.api.docs'))
        ->assertOk()
        ->assertSee(__('API documentation'))
        ->assertSee('/companies')
        ->assertSee('X-FlowManager-Signature');
});

test('api documentation renders correctly in english locale', function () {
    $administrator = apiDocsUser();

    app()->setLocale('en');

    $this->actingAs($administrator)
        ->get(route('integrations.api.docs'))
        ->assertOk()
        ->assertSee('Pagination')
        ->assertSee('/companies')
        ->assertSee('X-FlowManager-Signature');
});

test('viewer cannot view api documentation page', function () {
    $viewer = apiDocsUser('viewer');

    $this->actingAs($viewer)
        ->get(route('integrations.api.docs'))
        ->assertForbidden();
});
