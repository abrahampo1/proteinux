<?php

namespace App\Exceptions;

use Exception;

class AiProviderException extends Exception
{
    public function __construct(
        string $message,
        public readonly string $provider,
        public readonly int $statusCode = 0,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function authenticationFailed(string $provider): self
    {
        return new self(
            "El proveedor {$provider} rechazó la API key. Verifica que sea válida en /ajustes-ia.",
            $provider,
            401,
        );
    }

    public static function upstreamError(string $provider, int $status, string $detail = ''): self
    {
        $suffix = $detail !== '' ? " ({$detail})" : '';

        return new self(
            "Error del proveedor {$provider} (HTTP {$status}){$suffix}.",
            $provider,
            $status,
        );
    }

    public static function connectionFailed(string $provider, string $reason): self
    {
        return new self(
            "No se pudo conectar con {$provider}: {$reason}",
            $provider,
            0,
        );
    }

    public static function unexpectedShape(string $provider): self
    {
        return new self(
            "El proveedor {$provider} devolvió una respuesta en un formato inesperado.",
            $provider,
            502,
        );
    }
}
