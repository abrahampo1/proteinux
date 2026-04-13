<?php

use App\Models\PredictedJob;
use App\Models\Scientific\ScientificDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the documents index page', function () {
    $this->get('/documentos')->assertOk();
});

it('lists documents', function () {
    $user = User::factory()->create();
    ScientificDocument::factory()->for($user)->create(['title' => 'Paper sobre insulina']);
    ScientificDocument::factory()->for($user)->create(['title' => 'Apunte de hemoglobina']);

    $this->get('/documentos')
        ->assertOk()
        ->assertSee('Paper sobre insulina')
        ->assertSee('Apunte de hemoglobina');
});

it('filters documents by type', function () {
    $user = User::factory()->create();
    ScientificDocument::factory()->for($user)->create(['title' => 'Paper tipo paper', 'type' => 'paper']);
    ScientificDocument::factory()->for($user)->create(['title' => 'Nota tipo note', 'type' => 'note']);

    $this->get('/documentos?type=paper')
        ->assertOk()
        ->assertSee('Paper tipo paper')
        ->assertDontSee('Nota tipo note');
});

it('searches documents by title', function () {
    $user = User::factory()->create();
    ScientificDocument::factory()->for($user)->create(['title' => 'Cristalografia de lisozima']);
    ScientificDocument::factory()->for($user)->create(['title' => 'Dinamica molecular']);

    $this->get('/documentos?search=lisozima')
        ->assertOk()
        ->assertSee('Cristalografia de lisozima')
        ->assertDontSee('Dinamica molecular');
});

it('shows document creation form to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/documentos/nuevo')
        ->assertOk();
});

it('redirects guests from document creation', function () {
    $this->get('/documentos/nuevo')->assertRedirect('/acceso');
});

it('creates a document with metadata', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/documentos', [
        'title' => 'Nuevo paper de prueba',
        'description' => 'Descripcion del paper.',
        'type' => 'paper',
        'url' => 'https://doi.org/10.1234/test',
        'doi' => '10.1234/test',
    ])->assertRedirect();

    $doc = ScientificDocument::where('title', 'Nuevo paper de prueba')->first();
    expect($doc)->not->toBeNull()
        ->and($doc->type)->toBe('paper')
        ->and($doc->doi)->toBe('10.1234/test')
        ->and($doc->user_id)->toBe($user->id);
});

it('creates a document linked to proteins', function () {
    $user = User::factory()->create();
    $job1 = PredictedJob::create([
        'sequence_hash' => str_repeat('a', 64),
        'job_id' => 'job-1',
        'fasta_sequence' => '>test1\nMVLSP',
        'completed_at' => now(),
    ]);
    $job2 = PredictedJob::create([
        'sequence_hash' => str_repeat('b', 64),
        'job_id' => 'job-2',
        'fasta_sequence' => '>test2\nMVLSP',
        'completed_at' => now(),
    ]);

    $this->actingAs($user)->post('/documentos', [
        'title' => 'Paper con proteinas',
        'type' => 'paper',
        'protein_ids' => [$job1->id, $job2->id],
    ])->assertRedirect();

    $doc = ScientificDocument::where('title', 'Paper con proteinas')->first();
    expect($doc->proteins)->toHaveCount(2);
});

it('shows a document detail page', function () {
    $user = User::factory()->create();
    $doc = ScientificDocument::factory()->for($user)->create(['title' => 'Detalle de paper']);

    $this->get('/documentos/'.$doc->id)
        ->assertOk()
        ->assertSee('Detalle de paper');
});

it('validates required fields', function () {
    $this->actingAs(User::factory()->create())
        ->post('/documentos', ['title' => '', 'type' => ''])
        ->assertSessionHasErrors(['title', 'type']);
});

it('redirects to URL on download if no file', function () {
    $user = User::factory()->create();
    $doc = ScientificDocument::factory()->for($user)->create([
        'url' => 'https://example.com/paper.pdf',
    ]);

    $this->get('/documentos/'.$doc->id.'/descargar')
        ->assertRedirect('https://example.com/paper.pdf');
});
