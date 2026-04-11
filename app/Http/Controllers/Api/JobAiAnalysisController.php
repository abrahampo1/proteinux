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

class JobAiAnalysisController extends Controller
{
    private const ANALYSIS_PROMPT = <<<'PROMPT'
Analiza estos resultados de AlphaFold y estructura tu respuesta en secciones numeradas:

1) Calidad de la predicción — interpreta el pLDDT medio y la distribución; señala si el modelo es fiable globalmente o solo en partes.
2) Interpretación biológica — qué sugieren las propiedades (solubilidad, estabilidad, alertas) sobre el comportamiento de la proteína.
3) Regiones a investigar — dominios o residuos que merecen atención (apóyate en pLDDT bajo o PAE alto si está disponible).
4) Siguientes pasos — una o dos recomendaciones concretas (experimentales o computacionales).

Responde en español, en prosa clara, sin tecnicismos innecesarios. Máximo 350 palabras. No uses markdown: solo texto plano con saltos de línea entre secciones y el prefijo "1)", "2)", etc.
PROMPT;

    public function __invoke(
        string $jobId,
        CesgaApiService $api,
        AiProviderFactory $factory,
    ): JsonResponse {
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

        $accounting = null;
        try {
            $accounting = $api->getJobAccounting($jobId);
        } catch (CesgaApiException) {
            // accounting es opcional para el contexto
        }

        $system = "Eres un asistente experto en bioinformática estructural y análisis de predicciones de AlphaFold. Recibes los datos de un job y debes interpretarlos con precisión científica. A continuación, el informe del job actual:\n\n"
            .JobContextBuilder::build($outputs, $accounting);

        try {
            $analysis = $provider->chat($system, [[
                'role' => 'user',
                'content' => self::ANALYSIS_PROMPT,
            ]]);
        } catch (AiProviderException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => 'provider_error',
                'provider' => $provider->name(),
            ], $e->statusCode >= 400 && $e->statusCode < 600 ? $e->statusCode : 502);
        }

        return response()->json([
            'analysis' => $analysis,
            'provider' => $provider->name(),
            'model' => $provider->model(),
        ]);
    }
}
