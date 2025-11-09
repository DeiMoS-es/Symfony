<?php
namespace App\Module\Auth\Security;

use App\Module\Auth\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JwtTokenGenerator implements TokenGeneratorInterface
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function generateToken(User $user): string
    {
        // Lexik espera una instancia que implemente UserInterface (tu User ya lo hace)
        return $this->jwtManager->create($user);
    }
}
