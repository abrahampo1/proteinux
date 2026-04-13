<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the login page', function () {
    $this->get('/acceso')->assertOk();
});

it('logs in a user with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);

    $this->post('/acceso', [
        'email' => $user->email,
        'password' => 'secret123',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->post('/acceso', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out a user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/salir')
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('redirects guests from protected routes', function () {
    $this->get('/foro/nuevo')->assertRedirect('/acceso');
});
