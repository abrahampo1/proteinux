<?php

use App\Models\Forum\ForumThread;
use App\Models\PredictedJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the forum index', function () {
    $this->get('/foro')->assertOk();
});

it('shows forum threads', function () {
    $user = User::factory()->create();
    ForumThread::factory()->for($user)->create(['title' => 'Analisis de insulina']);
    ForumThread::factory()->for($user)->create(['title' => 'Estructura de hemoglobina']);

    $this->get('/foro')
        ->assertOk()
        ->assertSee('Analisis de insulina')
        ->assertSee('Estructura de hemoglobina');
});

it('searches threads by title', function () {
    $user = User::factory()->create();
    ForumThread::factory()->for($user)->create(['title' => 'Insulina recombinante']);
    ForumThread::factory()->for($user)->create(['title' => 'Hemoglobina alpha']);

    $this->get('/foro?q=Insulina')
        ->assertOk()
        ->assertSee('Insulina recombinante')
        ->assertDontSee('Hemoglobina alpha');
});

it('shows the thread creation form to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/foro/nuevo')
        ->assertOk();
});

it('redirects guests from thread creation', function () {
    $this->get('/foro/nuevo')->assertRedirect('/acceso');
});

it('creates a new thread', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/foro', [
        'title' => 'Nuevo hilo de prueba',
        'body' => 'Contenido del hilo de prueba sobre proteinas.',
    ])->assertRedirect();

    $thread = ForumThread::where('title', 'Nuevo hilo de prueba')->first();
    expect($thread)->not->toBeNull()
        ->and($thread->slug)->toBe('nuevo-hilo-de-prueba')
        ->and($thread->user_id)->toBe($user->id);
});

it('creates a thread linked to a protein', function () {
    $user = User::factory()->create();
    $job = PredictedJob::create([
        'sequence_hash' => str_repeat('x', 64),
        'job_id' => 'test-job-1',
        'fasta_sequence' => '>test\nMVLSP',
        'completed_at' => now(),
    ]);

    $this->actingAs($user)->post('/foro', [
        'title' => 'Hilo vinculado a proteina',
        'body' => 'Discusion sobre esta proteina.',
        'predicted_job_id' => $job->id,
    ])->assertRedirect();

    $thread = ForumThread::where('title', 'Hilo vinculado a proteina')->first();
    expect($thread->predicted_job_id)->toBe($job->id);
});

it('shows a thread with its posts', function () {
    $user = User::factory()->create();
    $thread = ForumThread::factory()->for($user)->create(['title' => 'Hilo con respuestas']);
    $thread->posts()->create([
        'body' => 'Primera respuesta al hilo.',
        'user_id' => $user->id,
    ]);

    $this->get('/foro/'.$thread->slug)
        ->assertOk()
        ->assertSee('Hilo con respuestas')
        ->assertSee('Primera respuesta al hilo.');
});

it('validates thread title and body', function () {
    $this->actingAs(User::factory()->create())
        ->post('/foro', ['title' => '', 'body' => ''])
        ->assertSessionHasErrors(['title', 'body']);
});
