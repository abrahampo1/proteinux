<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the registration page', function () {
    $this->get('/registro')->assertOk();
});

it('registers a new user', function () {
    $this->post('/registro', [
        'name' => 'Maria Garcia',
        'username' => 'mgarcia',
        'email' => 'maria@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('home'));

    expect(User::where('username', 'mgarcia')->exists())->toBeTrue();
    $this->assertAuthenticatedAs(User::where('username', 'mgarcia')->first());
});

it('rejects duplicate username', function () {
    User::factory()->create(['username' => 'testuser']);

    $this->post('/registro', [
        'name' => 'Duplicate',
        'username' => 'testuser',
        'email' => 'unique@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('username');
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post('/registro', [
        'name' => 'Duplicate',
        'username' => 'unique_user',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});

it('rejects invalid username format', function () {
    $this->post('/registro', [
        'name' => 'Bad User',
        'username' => 'bad user!',
        'email' => 'bad@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('username');
});

it('requires password confirmation', function () {
    $this->post('/registro', [
        'name' => 'No Confirm',
        'username' => 'noconfirm',
        'email' => 'noconfirm@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('password');
});
