<?php

namespace App\Http\Controllers;

use App\Exceptions\CesgaApiException;
use App\Models\Forum\ForumThread;
use App\Models\PredictedJob;
use App\Services\CesgaApiService;
use Illuminate\Support\Collection;
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

        try {
            $recentThreads = ForumThread::with(['user', 'remoteUser', 'predictedJob'])
                ->latest('last_activity_at')
                ->take(4)
                ->get();
        } catch (\Throwable) {
            $recentThreads = new Collection;
        }

        try {
            $recentPredictions = PredictedJob::whereNotNull('completed_at')
                ->latest('completed_at')
                ->take(4)
                ->get();
        } catch (\Throwable) {
            $recentPredictions = new Collection;
        }

        return view('home', compact('stats', 'samples', 'recentThreads', 'recentPredictions'));
    }
}
