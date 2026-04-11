<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CesgaApiException;
use App\Http\Controllers\Controller;
use App\Services\CesgaApiService;
use App\Services\JobLibrary;
use Illuminate\Http\JsonResponse;

class JobOutputsController extends Controller
{
    public function __invoke(string $jobId, CesgaApiService $api, JobLibrary $library): JsonResponse
    {
        try {
            $outputs = $api->getJobOutputs($jobId);
            // Mantiene la biblioteca sincronizada sin esperar a que el usuario
            // recargue /jobs/{id}. El polling del frontend llama a este
            // endpoint en cuanto el job pasa a COMPLETED.
            $library->enrichFromOutputs($jobId, $outputs);

            return response()->json($outputs);
        } catch (CesgaApiException $e) {
            return response()->json(['error' => $e->getMessage()], $e->statusCode ?: 500);
        }
    }
}
