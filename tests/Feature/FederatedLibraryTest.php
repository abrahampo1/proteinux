<?php

use App\Models\PredictedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows filter tabs on library page', function () {
    $this->get('/biblioteca')
        ->assertOk()
        ->assertSee('todos')
        ->assertSee('local')
        ->assertSee('federado');
});

it('filters local entries', function () {
    PredictedJob::create([
        'sequence_hash' => str_repeat('a', 64),
        'job_id' => 'local-1',
        'fasta_sequence' => '>local\nMVLSP',
        'protein_name' => 'Local Protein',
        'completed_at' => now(),
        'is_remote' => false,
    ]);
    PredictedJob::create([
        'sequence_hash' => str_repeat('b', 64),
        'job_id' => 'remote-peer-1',
        'fasta_sequence' => '',
        'protein_name' => 'Remote Protein',
        'completed_at' => now(),
        'is_remote' => true,
        'origin_domain' => 'peer.example.com',
        'origin_id' => '1',
    ]);

    $this->get('/biblioteca?source=local')
        ->assertOk()
        ->assertSee('Local Protein')
        ->assertDontSee('Remote Protein');
});

it('filters federated entries', function () {
    PredictedJob::create([
        'sequence_hash' => str_repeat('c', 64),
        'job_id' => 'local-2',
        'fasta_sequence' => '>local\nMVLSP',
        'protein_name' => 'Only Local',
        'completed_at' => now(),
        'is_remote' => false,
    ]);
    PredictedJob::create([
        'sequence_hash' => str_repeat('d', 64),
        'job_id' => 'remote-peer-2',
        'fasta_sequence' => '',
        'protein_name' => 'Only Remote',
        'completed_at' => now(),
        'is_remote' => true,
        'origin_domain' => 'peer2.example.com',
        'origin_id' => '2',
    ]);

    $this->get('/biblioteca?source=federated')
        ->assertOk()
        ->assertSee('Only Remote')
        ->assertDontSee('Only Local');
});

it('shares a library entry', function () {
    $user = User::factory()->create();
    $job = PredictedJob::create([
        'sequence_hash' => str_repeat('e', 64),
        'job_id' => 'shareable-1',
        'fasta_sequence' => '>test\nMVLSP',
        'protein_name' => 'Shareable Protein',
        'completed_at' => now(),
        'is_shared' => false,
    ]);

    $this->actingAs($user)->post('/biblioteca/'.$job->id.'/compartir')
        ->assertRedirect();

    expect($job->fresh()->is_shared)->toBeTrue();
});

it('shows origin badge for remote entries', function () {
    PredictedJob::create([
        'sequence_hash' => str_repeat('f', 64),
        'job_id' => 'remote-badge-1',
        'fasta_sequence' => '',
        'protein_name' => 'Remote With Badge',
        'completed_at' => now(),
        'is_remote' => true,
        'origin_domain' => 'peer.example.com',
        'origin_id' => '99',
    ]);

    $this->get('/biblioteca')
        ->assertOk()
        ->assertSee('peer.example.com');
});
