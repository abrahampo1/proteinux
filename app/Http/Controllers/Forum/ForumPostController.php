<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreForumPostRequest;
use App\Models\Forum\ForumPost;
use App\Models\Forum\ForumThread;
use Illuminate\Http\RedirectResponse;

class ForumPostController extends Controller
{
    public function store(StoreForumPostRequest $request, ForumThread $thread): RedirectResponse
    {
        $thread->posts()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        $thread->increment('posts_count');
        $thread->update(['last_activity_at' => now()]);

        return redirect()
            ->route('forum.show', $thread)
            ->with('success', 'Respuesta publicada.');
    }

    public function destroy(ForumPost $post): RedirectResponse
    {
        $thread = $post->thread;

        if ($post->user_id !== auth()->id()) {
            abort(403, 'No puedes eliminar esta respuesta.');
        }

        $post->delete();
        $thread->decrement('posts_count');

        return redirect()
            ->route('forum.show', $thread)
            ->with('success', 'Respuesta eliminada.');
    }
}
