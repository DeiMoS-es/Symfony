<?php

namespace App\Module\Auth\Service;

use App\Module\Auth\Entity\RefreshToken;
use App\Module\Auth\Repository\UserRepository;
use App\Module\Auth\Exception\InvalidCredentialsException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Module\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private EntityManagerInterface $em
    ) {}

    public function validateCredentials(string $email, string $plainPassword): User
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (!$user instanceof User || !$this->passwordHasher->isPasswordValid($user, $plainPassword)) {
            throw new InvalidCredentialsException();
        }

        return $user;
    }

    public function generateJwt(User $user): string
    {
        return $this->jwtManager->create($user);
    }

    public function generateRefreshToken(User $user): array
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

