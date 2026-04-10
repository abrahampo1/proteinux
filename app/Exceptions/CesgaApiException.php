<?php

namespace App\Exceptions;

use Exception;

class CesgaApiException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly array $responseBody = [],
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(int $statusCode, array $body): self
    {
        $message = $body['detail'] ?? $body['message'] ?? 'CESGA API error';

        if (is_array($message)) {
            $message = collect($message)->pluck('msg')->implode(', ');
        }

        return new self($message, $statusCode, $body);
    }

    public static function connectionFailed(string $reason): self
    {
        return new self("Could not connect to CESGA API: {$reason}", 0);
    }
}
