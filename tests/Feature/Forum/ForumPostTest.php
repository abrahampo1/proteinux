<?php

use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumThread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a reply to a thread', function () {
    $user = User::factory()->create();
    $thread = ForumThread::factory()->for($user)->create();

    $this->actingAs($user)->post('/foro/'.$thread->slug.'/respuestas', [
        'body' => 'Esta es mi respuesta.',
    ])->assertRedirect(route('forum.show', $thread));

    expect(ForumPost::where('body', 'Esta es mi respuesta.')->exists())->toBeTrue();
    expect($thread->fresh()->posts_count)->toBe(1);
});

it('rejects guest replies', function () {
    $thread = ForumThread::factory()->for(User::factory())->create();

    $this->post('/foro/'.$thread->slug.'/respuestas', [
        'body' => 'Respuesta de invitado.',
    ])->assertRedirect('/acceso');
});

it('deletes own post', function () {
    $user = User::factory()->create();
    $thread = ForumThread::factory()->for($user)->create(['posts_count' => 1]);
    $post = ForumPost::create([
        'forum_thread_id' => $thread->id,
        'body' => 'Post a eliminar.',
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)->delete('/foro/respuestas/'.$post->id)
        ->assertRedirect(route('forum.show', $thread));

    expect(ForumPost::find($post->id))->toBeNull();
    expect($thread->fresh()->posts_count)->toBe(0);
});

it('prevents deleting another users post', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $thread = ForumThread::factory()->for($user1)->create();
    $post = ForumPost::create([
        'forum_thread_id' => $thread->id,
        'body' => 'Post de otro usuario.',
        'user_id' => $user1->id,
    ]);

    $this->actingAs($user2)->delete('/foro/respuestas/'.$post->id)
        ->assertForbidden();
});

it('validates reply body', function () {
    $user = User::factory()->create();
    $thread = ForumThread::factory()->for($user)->create();

    $this->actingAs($user)->post('/foro/'.$thread->slug.'/respuestas', [
        'body' => '',
    ])->assertSessionHasErrors('body');
});
