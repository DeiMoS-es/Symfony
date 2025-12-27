<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\Exception\InvalidCredentialsException;
use App\Module\Auth\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/auth/login', name: 'auth_login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthService $authService, Security $security): Response
    {
        $context = [
            'error' => null,
            'email' => '',
        ];

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
            // 1. Validar credenciales
            $user = $authService->validateCredentials($email, $password);

            // 2. Iniciar sesión en el Firewall 'main' (para que el Dashboard te reconozca)
            $security->login($user, 'security.authenticator.form_login.main', 'main');

            // 3. Generar tokens para compatibilidad con API
            $jwt = $authService->generateJwt($user);
            $refresh = $authService->generateRefreshToken($user);

            // 4. Crear respuesta de redirección
            $response = $this->redirectToRoute('user_dashboard');

            // 5. Añadir cookies a la respuesta
            $response->headers->setCookie(Cookie::create(
                'ACCESS_TOKEN',
                $jwt,
                (new \DateTimeImmutable())->add(new \DateInterval('PT1H')),
                '/',
                null,
                true,
                false,
                false,
                'Strict'
            ));

            $response->headers->setCookie(Cookie::create(
                'REFRESH_TOKEN',
                $refresh['plain'],
                $refresh['expires'],
                '/',
                null,
                true,
                true,
                false,
                'Strict'
            ));

            return $response;
            
        } catch (InvalidCredentialsException $e) {
            $context['error'] = 'Credenciales inválidas';
        } catch (\Throwable $e) {
            $context['error'] = 'Error interno: ' . $e->getMessage();
        }

        return $this->render('auth/login.html.twig', $context);
    }
}