<?php

namespace App\Services\Ai;

use App\Exceptions\AiNotConfiguredException;
use App\Support\Ai\AiSettings;

class AiProviderFactory
{
    public function __construct(private readonly AiSettings $settings) {}

    /**
     * @throws AiNotConfiguredException
     */
    public function makeActive(): AiProvider
    {
        $provider = $this->settings->activeProvider();

        if ($provider === null) {
            throw new AiNotConfiguredException('Selecciona un proveedor de IA en /ajustes-ia.');
        }

        $apiKey = $this->settings->apiKeyFor($provider);

        if ($apiKey === null) {
            throw new AiNotConfiguredException(
                "El proveedor {$provider} no tiene API key configurada. Añádela en /ajustes-ia."
            );
        }

        $model = $this->settings->modelFor($provider);

        return match ($provider) {
            'anthropic' => new AnthropicProvider($apiKey, $model),
            'openai' => new OpenAiProvider($apiKey, $model),
            'gemini' => new GeminiProvider($apiKey, $model),
            default => throw new AiNotConfiguredException("Proveedor desconocido: {$provider}"),
        };
    }
}
