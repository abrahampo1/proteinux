<?php

namespace App\Services\Ai;

use App\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class AnthropicProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function name(): string
    {
        return 'anthropic';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function chat(string $system, array $messages): string
    {
        $baseUrl = (string) config('services.llm.anthropic.base_url');
        $timeout = (int) config('services.llm.timeout', 60);

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout($timeout)
                ->acceptJson()
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                ])
                ->post('/v1/messages', [
                    'model' => $this->model,
                    'system' => $system,
                    'max_tokens' => 1024,
                    'messages' => array_map(
                        fn (array $m) => ['role' => $m['role'], 'content' => $m['content']],
                        $messages,
                    ),
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

        $text = $response->json('content.0.text');

        if (! is_string($text) || $text === '') {
            throw AiProviderException::unexpectedShape($this->name());
        }

        return $text;
    }
}
