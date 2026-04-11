<?php

namespace App\Exceptions;

use Exception;

class AiNotConfiguredException extends Exception
{
    public function __construct(string $message = 'No hay un proveedor de IA configurado.')
    {
        parent::__construct($message, 409);
    }
}
