<?php
namespace App\Module\Auth\Exception;

use RuntimeException;

class UserAlreadyExistsException extends RuntimeException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Ya existe un usuario con el email "%s".', $email));
    }
}
