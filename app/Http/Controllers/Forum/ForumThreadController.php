<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreForumThreadRequest;
use App\Models\Forum\ForumThread;
use App\Models\PredictedJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumThreadController extends Controller
{
    public function index(Request $request): View
    {
        $query = ForumThread::with(['user', 'remoteUser', 'predictedJob'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_activity_at');

        if ($search = $request->string('q')->toString()) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($proteinId = $request->integer('protein')) {
            $query->where('predicted_job_id', $proteinId);
        }

        $threads = $query->paginate(20)->withQueryString();

        return view('forum.index', [
            'threads' => $threads,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $predictedJobs = PredictedJob::whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->get();

        return view('forum.create', [
            'predictedJobs' => $predictedJobs,
        ]);
    }

    public function store(StoreForumThreadRequest $request): RedirectResponse
    {
        $thread = ForumThread::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('forum.show', $thread)
            ->with('success', 'Hilo creado correctamente.');
    }

    public function show(ForumThread $thread): View
    {
        $thread->load(['user', 'remoteUser', 'predictedJob']);

        $posts = $thread->posts()
            ->with(['user', 'remoteUser', 'replies.user', 'replies.remoteUser'])
            ->whereNull('parent_id')
            ->oldest()
            ->paginate(50);

        return view('forum.show', [
            'thread' => $thread,
            'posts' => $posts,
        ]);
    }
}
