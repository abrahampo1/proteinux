<?php

namespace App\Services\Ai;

use App\Exceptions\AiProviderException;

interface AiProvider
{
    public function name(): string;

    public function model(): string;

    /**
     * Send a chat request and return the assistant's reply as plain text.
     *
     * @param  string  $system  System instruction sent with every turn.
     * @param  list<array{role: 'user'|'assistant', content: string}>  $messages
     *
     * @throws AiProviderException
     */
    public function chat(string $system, array $messages): string;
}
