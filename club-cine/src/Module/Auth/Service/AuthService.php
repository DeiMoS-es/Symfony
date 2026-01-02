<?php

namespace App\Module\Auth\Service;

use App\Module\Auth\DTO\LoginRequest;
use App\Module\Auth\Entity\RefreshToken;
use App\Module\Auth\Entity\User;
use App\Module\Auth\Exception\InvalidCredentialsException;
use App\Module\Auth\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * Valida las credenciales usando el DTO
     */
    public function validateCredentials(LoginRequest $dto): User
    {
        $user = $this->userRepository->findOneByEmail($dto->getEmail());

        if (!$user instanceof User || !$this->passwordHasher->isPasswordValid($user, $dto->getPassword())) {
            throw new InvalidCredentialsException();
        }

        return $user;
    }

    /**
     * Centraliza la creación de cookies en la respuesta
     */
    public function authenticateResponse(Response $response, User $user): void
    {
        $jwt = $this->generateJwt($user);
        $refresh = $this->generateRefreshToken($user);

        // Cookie de Acceso (JWT)
        $response->headers->setCookie(Cookie::create(
            'ACCESS_TOKEN',
            $jwt,
            (new \DateTimeImmutable())->add(new \DateInterval('PT1H')),
            '/', null, true, false, false, 'Strict'
        ));

        // Cookie de Refresco (Solo accesible por HTTP)
        $response->headers->setCookie(Cookie::create(
            'REFRESH_TOKEN',
            $refresh['plain'],
            $refresh['expires'],
            '/', null, true, true, false, 'Strict'
        ));
    }

    private function generateJwt(User $user): string
    {
        return $this->jwtManager->create($user);
    }

    private function generateRefreshToken(User $user): array
    {
        $plain = bin2hex(random_bytes(64));
        $hash = password_hash($plain, PASSWORD_DEFAULT);
        $expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P30D'));

        $refreshToken = new RefreshToken($user, $hash, $expiresAt);
        $this->em->persist($refreshToken);
        $this->em->flush();

        return [
            'plain' => $plain,
            'expires' => $expiresAt,
        ];
    }
}
?>