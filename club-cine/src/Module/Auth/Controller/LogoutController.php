<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogoutController extends AbstractController
{
    #[Route('/auth/logout', name: 'auth_logout', methods: ['GET'])]
    public function logout(
        Request $request,
        EntityManagerInterface $em,
        RefreshTokenRepository $refreshRepo
    ): JsonResponse {
        $cookie = $request->cookies->get('REFRESH_TOKEN');
        if ($cookie) {
            $rt = $refreshRepo->findOneActiveByTokenPlain($cookie);
            if ($rt) {
                $rt->revoke();
                $em->persist($rt);
                $em->flush();
            }
        }

        // Invalidate session if present
        if ($request->hasSession()) {
            try {
                $session = $request->getSession();
                $session->invalidate();
            } catch (\Throwable $e) {
                // no-op: ensure logout proceeds even if session handling fails
            }
        }

        // Expire REFRESH_TOKEN cookie (HttpOnly)
        $expiredRefresh = Cookie::create(
            'REFRESH_TOKEN',
            '',
            (new \DateTimeImmutable())->sub(new \DateInterval('P1D')),
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );

        // Expire ACCESS_TOKEN cookie (not HttpOnly in our current login implementation)
        $expiredAccess = Cookie::create(
            'ACCESS_TOKEN',
            '',
            (new \DateTimeImmutable())->sub(new \DateInterval('P1D')),
            '/',
            null,
            true,
            false,
            false,
            'Strict'
        );

        // Expire PHP session cookie (session_name(), typically PHPSESSID)
        $sessionCookieName = \session_name();
        $expiredSession = Cookie::create(
            $sessionCookieName,
            '',
            (new \DateTimeImmutable())->sub(new \DateInterval('P1D')),
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );

        $response = new JsonResponse(['ok' => true]);
        $response->headers->setCookie($expiredRefresh);
        $response->headers->setCookie($expiredAccess);
        $response->headers->setCookie($expiredSession);
        return $response;
    }
}
