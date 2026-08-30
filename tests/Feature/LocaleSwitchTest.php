<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest users can change the interface language', function () {
    $response = $this
        ->from(route('login'))
        ->post(route('locale.update', 'it'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('locale', 'it');
});

test('selected language is applied to the login page', function () {
    $this->withSession(['locale' => 'it'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Accedi')
        ->assertSee('Indirizzo email');
});

test('unsupported interface languages are rejected', function () {
    $this->post(route('locale.update', 'fr'))
        ->assertNotFound();
});
