<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\Mapper\AuthMapper;
use App\Module\Auth\Service\AuthService;
use App\Module\Auth\Exception\InvalidCredentialsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly Security $security,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('/auth/login', name: 'auth_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('auth/login.html.twig', ['email' => '']);
        }

        // 1. CSRF
        if (!$this->isCsrfTokenValid('auth_login', $request->request->get('_csrf_token'))) {
            return $this->renderError('Token CSRF inválido.', $request->request->get('email'));
        }

        // 2. Mapeo y Validación con el DTO
        $loginDto = AuthMapper::fromRequest($request);
        $violations = $this->validator->validate($loginDto);

        if (count($violations) > 0) {
            return $this->renderError($violations[0]->getMessage(), $loginDto->getEmail());
        }

        try {
            // 3. Autenticación
            $user = $this->authService->validateCredentials($loginDto);

            // 4. Iniciar sesión en el Firewall 'main'
            $this->security->login($user, 'security.authenticator.form_login.main', 'main');

            // 5. Generar respuesta y adjuntar tokens/cookies
            $response = $this->redirectToRoute('user_dashboard');
            $this->authService->authenticateResponse($response, $user);

            return $response;

        } catch (InvalidCredentialsException) {
            return $this->renderError('Credenciales incorrectas.', $loginDto->getEmail());
        } catch (\Throwable $e) {
            return $this->renderError('Error interno del servidor.', $loginDto->getEmail());
        }
    }

    private function renderError(string $message, ?string $email): Response
    {
        return $this->render('auth/login.html.twig', [
            'error' => $message,
            'email' => $email ?? '',
        ]);
    }
}