<?php
namespace App\Module\Auth\Exception;

use RuntimeException;

class InvalidCredentialsException extends RuntimeException
{
    public function __construct(string $message = 'Credenciales inválidas')
    {
        parent::__construct($message);
    }
}
