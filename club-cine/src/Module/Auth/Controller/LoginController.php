<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\Entity\User;
use App\Module\Auth\Entity\RefreshToken;
use App\Module\Auth\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordEncoder,
        RefreshTokenRepository $refreshRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user || !$passwordEncoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        // Crear JWT
        $jwt = $jwtManager->create($user);

        // Crear refresh token
        $refreshPlain = bin2hex(random_bytes(64));
        $refreshHash = password_hash($refreshPlain, PASSWORD_DEFAULT);
        $expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P30D'));

        $refreshToken = new RefreshToken($user, $refreshHash, $expiresAt);
        $em->persist($refreshToken);
        $em->flush();

        // Crear cookie HttpOnly
        $cookie = Cookie::create('REFRESH_TOKEN', $refreshPlain, $expiresAt, '/', null, true, true, false, 'Strict');

        $response = new JsonResponse(['token' => $jwt]);
        $response->headers->setCookie($cookie);
        return $response;
    }
}
