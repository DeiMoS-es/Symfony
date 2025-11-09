<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\Entity\RefreshToken;
use App\Module\Auth\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RefreshController extends AbstractController
{
    #[Route('/auth/refresh', name: 'auth_refresh', methods: ['POST'])]
    public function refresh(
        Request $request,
        EntityManagerInterface $em,
        RefreshTokenRepository $refreshRepo,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $cookie = $request->cookies->get('REFRESH_TOKEN');
        if (!$cookie) {
            return new JsonResponse(['error' => 'No refresh token'], 401);
        }

        $rt = $refreshRepo->findOneActiveByTokenPlain($cookie);
        if (!$rt) {
            return new JsonResponse(['error' => 'Invalid refresh token'], 401);
        }

        // Rotación
        $rt->revoke();
        $em->persist($rt);

        $user = $rt->getUser();
        $newJwt = $jwtManager->create($user);

        $newRefreshPlain = bin2hex(random_bytes(64));
        $newRefreshHash = password_hash($newRefreshPlain, PASSWORD_DEFAULT);
        $newExpiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P30D'));
        $newRt = new RefreshToken($user, $newRefreshHash, $newExpiresAt);
        $em->persist($newRt);
        $em->flush();

        $cookie = Cookie::create('REFRESH_TOKEN', $newRefreshPlain, $newExpiresAt, '/', null, true, true, false, 'Strict');

        $response = new JsonResponse(['token' => $newJwt]);
        $response->headers->setCookie($cookie);
        return $response;
    }
}
