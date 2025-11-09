<?php

namespace App\Module\Auth\Repository;

use App\Module\Auth\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    /**
     * Busca un refresh token activo por el token en claro (comparando hashes)
     */
    public function findOneActiveByTokenPlain(string $plainToken): ?RefreshToken
    {
        $tokens = $this->findBy(['revokedAt' => null]);

        foreach ($tokens as $token) {
            if (!$token->isRevoked() && password_verify($plainToken, $token->getTokenHash())) {
                return $token;
            }
        }

        return null;
    }
}
