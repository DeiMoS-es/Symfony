<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\Exception\InvalidCredentialsException;
use App\Module\Auth\Service\AuthService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/auth/login', name: 'auth_login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthService $authService): Response
    {
        $context = ['error' => null,'email' => '',];

        if ($request->isMethod('GET')) {
            return $this->render('auth/login.html.twig', $context);
        }

        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('auth_login', $csrfToken)) {
            $context['error'] = 'Token CSRF inválido.';
            return $this->render('auth/login.html.twig', $context);
        }

        $email = (string) $request->request->get('email', '');
        $password = (string) $request->request->get('password', '');
        $context['email'] = $email;

        try {
            $user = $authService->validateCredentials($email, $password);
            $jwt = $authService->generateJwt($user);
            $refresh = $authService->generateRefreshToken($user);

            // Guardar el JWT en una cookie accesible por JS (si lo necesitas)
            $jwtCookie = Cookie::create(
                'ACCESS_TOKEN',
                $jwt,
                (new \DateTimeImmutable())->add(new \DateInterval('PT1H')),
                '/',
                null,
                true,  // secure
                false, // httpOnly = false para que JS pueda leerlo si lo necesitas
                false,
                'Strict'
            );

            // Guardar el refresh token en una cookie HttpOnly
            $refreshCookie = Cookie::create(
                'REFRESH_TOKEN',
                $refresh['plain'],
                $refresh['expires'],
                '/',
                null,
                true,
                true, // httpOnly
                false,
                'Strict'
            );

            // TODO hacer redirección a ruta protegida
            //$response = $this->render('home.html.twig', ['user' => $user]);
            $response = $this->redirectToRoute('user_dashboard');
            $response->headers->setCookie($jwtCookie);
            $response->headers->setCookie($refreshCookie);
            return $response;
            
        } catch (InvalidCredentialsException $e) {
            $context['error'] = 'Credenciales inválidas';
        } catch (\Throwable $e) {
           $context['error'] = 'Error interno del servidor: ' . $e->getMessage();
        }

        return $this->render('auth/login.html.twig', $context);
    }
}