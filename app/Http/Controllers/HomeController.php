<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Models\Forum\ForumThread;
use App\Models\PredictedJob;
use App\Services\CesgaApiService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CesgaApiService $api): View
    {
        try {
            $stats = $api->getProteinStats();
            $samples = $api->getSampleSequences();
        } catch (CesgaApiException) {
            $stats = null;
            $samples = [];
        }

        $recentThreads = ForumThread::with(['user', 'remoteUser', 'predictedJob'])
            ->orderByDesc('last_activity_at')
            ->limit(5)
            ->get();

        $libraryCount = PredictedJob::count();
        $completedCount = PredictedJob::whereNotNull('completed_at')->count();
        $threadCount = ForumThread::count();

        return view('home', compact(
            'stats',
            'samples',
            'recentThreads',
            'libraryCount',
            'completedCount',
            'threadCount',
        ));
    }
}
