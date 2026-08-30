<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest users are redirected from home to login', function () {
    $response = $this->get(
        route('home')
    );

    $response->assertRedirect(
        route('login')
    );
});

test('authenticated users are redirected from home to dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('home'));

    $response->assertRedirect(
        route('dashboard')
    );
});

test('login page is accessible to guest users', function () {
    $this->assertGuest();

    $response = $this->get(
        route('login')
    );

    $response
        ->assertOk()
        ->assertViewIs('auth.login');
});