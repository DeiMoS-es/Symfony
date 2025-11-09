<?php
namespace App\Module\Auth\Security;

use App\Module\Auth\Entity\User;

interface TokenGeneratorInterface
{
    /**
     * Genera un token (JWT u otro) para el usuario dado.
     *
     * @return string Token (JWT)
     */
    public function generateToken(User $user): string;
}
