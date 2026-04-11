<?php

namespace App\Services\Ai;

use App\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function name(): string
    {
        return 'gemini';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function chat(string $system, array $messages): string
    {
        $baseUrl = (string) config('services.llm.gemini.base_url');
        $timeout = (int) config('services.llm.timeout', 60);

        $contents = array_map(
            fn (array $m) => [
                'role' => $m['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m['content']]],
            ],
            $messages,
        );

        $endpoint = "/v1beta/models/{$this->model}:generateContent";

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout($timeout)
                ->acceptJson()
                ->withQueryParameters(['key' => $this->apiKey])
                ->post($endpoint, [
                    'systemInstruction' => [
                        'parts' => [['text' => $system]],
                    ],
                    'contents' => $contents,
                ]);
        } catch (\Throwable $e) {
            throw AiProviderException::connectionFailed($this->name(), $e->getMessage());
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw AiProviderException::authenticationFailed($this->name());
        }

        if ($response->failed()) {
            $detail = $response->json('error.message') ?? '';
            throw AiProviderException::upstreamError($this->name(), $response->status(), (string) $detail);
        }

        $parts = $response->json('candidates.0.content.parts');

        if (! is_array($parts)) {
            throw AiProviderException::unexpectedShape($this->name());
        }

        $text = collect($parts)
            ->pluck('text')
            ->filter(fn ($t) => is_string($t) && $t !== '')
            ->implode('');

        if ($text === '') {
            throw AiProviderException::unexpectedShape($this->name());
        }

        return $text;
    }
}
