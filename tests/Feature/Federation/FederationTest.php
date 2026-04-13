<?php

use App\Models\Federation\FederationInstance;
use App\Models\Federation\FederationKeypair;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns instance info at the federation endpoint', function () {
    config([
        'services.federation.enabled' => true,
        'services.federation.domain' => 'test.local',
        'services.federation.instance_name' => 'Test Instance',
    ]);

    $this->getJson('/federation/instance-info')
        ->assertOk()
        ->assertJsonStructure(['domain', 'name', 'public_key', 'proteinux_version'])
        ->assertJsonFragment(['domain' => 'test.local']);
});

it('returns 404 for federation inbox when disabled', function () {
    config(['services.federation.enabled' => false]);

    $this->postJson('/federation/inbox', ['type' => 'test'])
        ->assertNotFound();
});

it('shows the admin federation panel to admins', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/federacion')
        ->assertOk();
});

it('denies federation admin to non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/federacion')
        ->assertForbidden();
});

it('denies federation admin to guests', function () {
    $this->get('/admin/federacion')
        ->assertRedirect('/acceso');
});

it('generates a keypair via artisan command', function () {
    $this->artisan('federation:keygen')
        ->assertExitCode(0);

    expect(FederationKeypair::count())->toBe(1);
});

it('shows federation status via artisan command', function () {
    FederationInstance::factory()->create(['domain' => 'peer1.example.com']);
    FederationInstance::factory()->create(['domain' => 'peer2.example.com']);

    $this->artisan('federation:status')
        ->assertExitCode(0);
});
