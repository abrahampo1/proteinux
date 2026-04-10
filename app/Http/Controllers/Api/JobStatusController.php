<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CesgaApiException;
use App\Http\Controllers\Controller;
use App\Services\CesgaApiService;
use Illuminate\Http\JsonResponse;

class JobStatusController extends Controller
{
    public function __invoke(string $jobId, CesgaApiService $api): JsonResponse
    {
        try {
            return response()->json($api->getJobStatus($jobId));
        } catch (CesgaApiException $e) {
            return response()->json(['error' => $e->getMessage()], $e->statusCode ?: 500);
        }
    }
}
