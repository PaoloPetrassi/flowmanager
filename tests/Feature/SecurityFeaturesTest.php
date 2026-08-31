<?php

use App\Models\LoginActivity;
use App\Models\Role;
use App\Models\User;
use App\Services\TotpService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function securityFeatureUser(string $roleSlug, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

    return $user;
}

test('guest can request a password reset link', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->get(route('password.request'))->assertOk();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('failed logins are recorded', function () {
    $user = User::factory()->create(['email' => 'security@example.test']);

    $this->post(route('login.attempt'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertDatabaseHas('login_activities', [
        'user_id' => $user->id,
        'email' => $user->email,
        'event' => 'login_failed',
    ]);
});

test('user can enable two factor authentication', function () {
    $user = securityFeatureUser('operator');

    $this->actingAs($user)
        ->post(route('security.two-factor.begin'), ['current_password' => 'password'])
        ->assertRedirect();

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();

    $totp = app(TotpService::class);
    $code = $totp->at($user->two_factor_secret, (int) floor(time() / 30));

    $this->actingAs($user)
        ->post(route('security.two-factor.confirm'), ['code' => $code])
        ->assertRedirect();

    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

test('two factor enabled user must complete challenge after password login', function () {
    $user = securityFeatureUser('operator');
    $totp = app(TotpService::class);
    $secret = $totp->generateSecret();

    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->post(route('login.attempt'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.challenge'));

    $this->assertGuest();

    $code = $totp->at($secret, (int) floor(time() / 30));

    $this->post(route('two-factor.verify'), ['code' => $code])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect(LoginActivity::query()->where('user_id', $user->id)->where('event', 'login')->exists())->toBeTrue();
});

test('administrator can be required to configure two factor authentication', function () {
    config()->set('flowmanager.security.two_factor_required_for_administrators', true);
    $administrator = securityFeatureUser('administrator');

    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertRedirect(route('security.index'));

    $this->actingAs($administrator)
        ->get(route('security.index'))
        ->assertOk();
});

test('email verification can be enforced by configuration', function () {
    config()->set('flowmanager.security.require_email_verification', true);
    $user = securityFeatureUser('viewer', ['email_verified_at' => null]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk();

    $user->markEmailAsVerified();

    $this->actingAs($user->fresh())
        ->get(route('dashboard'))
        ->assertOk();
});
