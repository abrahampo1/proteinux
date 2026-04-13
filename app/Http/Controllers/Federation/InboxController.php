<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Jobs\Federation\ProcessInboundActivity;
use App\Models\Federation\FederationActivity;
use App\Services\Federation\FederationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    /**
     * Receive an inbound federation activity.
     */
    public function __invoke(Request $request, FederationService $federationService): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'action' => 'required|string',
            'data' => 'required|array',
            'source_domain' => 'required|string',
        ]);

        $activity = FederationActivity::create([
            'direction' => 'inbound',
            'type' => $request->input('type'),
            'payload' => $request->all(),
            'source_domain' => $request->input('source_domain'),
            'target_domain' => $federationService->localDomain(),
            'status' => 'pending',
            'attempts' => 0,
        ]);

        ProcessInboundActivity::dispatch($activity);

        return response()->json(['status' => 'accepted'], 202);
    }
}
