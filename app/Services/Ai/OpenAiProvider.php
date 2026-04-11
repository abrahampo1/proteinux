<?php

namespace App\Services\Ai;

use App\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function name(): string
    {
        return 'openai';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function chat(string $system, array $messages): string
    {
        $baseUrl = (string) config('services.llm.openai.base_url');
        $timeout = (int) config('services.llm.timeout', 60);

        $payloadMessages = array_merge(
            [['role' => 'system', 'content' => $system]],
            array_map(
                fn (array $m) => ['role' => $m['role'], 'content' => $m['content']],
                $messages,
            ),
        );

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout($timeout)
                ->acceptJson()
                ->withToken($this->apiKey)
                ->post('/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => $payloadMessages,
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

        $text = $response->json('choices.0.message.content');

        if (! is_string($text) || $text === '') {
            throw AiProviderException::unexpectedShape($this->name());
        }

        return $text;
    }
}
