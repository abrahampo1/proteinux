<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AiNotConfiguredException;
use App\Exceptions\AiProviderException;
use App\Exceptions\CesgaApiException;
use App\Http\Controllers\Controller;
use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\JobContextBuilder;
use App\Services\CesgaApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobAiChatController extends Controller
{
    public function __invoke(
        string $jobId,
        Request $request,
        CesgaApiService $api,
        AiProviderFactory $factory,
    ): JsonResponse {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:20'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'min:1', 'max:4000'],
        ]);

        $messages = $validated['messages'];

        if (($messages[array_key_last($messages)]['role'] ?? null) !== 'user') {
            throw ValidationException::withMessages([
                'messages' => 'El último mensaje debe ser del usuario.',
            ]);
        }

        try {
            $provider = $factory->makeActive();
        } catch (AiNotConfiguredException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => 'not_configured',
            ], 409);
        }

        try {
            $outputs = $api->getJobOutputs($jobId);
        } catch (CesgaApiException $e) {
            return response()->json([
                'error' => 'No se pudieron cargar los resultados del job: '.$e->getMessage(),
                'code' => 'cesga_error',
            ], $e->statusCode ?: 502);
        }

        $system = 'Eres un asistente que responde preguntas sobre los resultados de este job de AlphaFold. '
            .'Sé preciso y cita los datos del informe cuando aplique. Si te preguntan algo fuera del dominio '
            .'biológico/estructural o que no pueda deducirse de los datos, dilo claramente y sugiere qué '
            .'información adicional haría falta. Responde en español, en prosa clara y concisa. '
            ."No uses markdown: solo texto plano.\n\n"
            .'INFORME DEL JOB:'."\n\n"
            .JobContextBuilder::build($outputs);

        try {
            $reply = $provider->chat($system, $messages);
        } catch (AiProviderException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => 'provider_error',
                'provider' => $provider->name(),
            ], $e->statusCode >= 400 && $e->statusCode < 600 ? $e->statusCode : 502);
        }

        return response()->json([
            'reply' => $reply,
            'provider' => $provider->name(),
            'model' => $provider->model(),
        ]);
    }
}
