<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Services\Federation\FederationService;
use Illuminate\Http\JsonResponse;

class InstanceInfoController extends Controller
{
    /**
     * Return this instance's federation info.
     */
    public function __invoke(FederationService $federationService): JsonResponse
    {
        return response()->json($federationService->localInstanceInfo());
    }
}
