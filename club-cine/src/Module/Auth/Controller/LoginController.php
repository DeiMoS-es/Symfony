<?php
declare(strict_types=1);

namespace App\Module\Auth\Controller;

use App\Module\Auth\DTO\LoginRequest;
use App\Module\Auth\Exception\InvalidCredentialsException;
use App\Module\Auth\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class LoginController extends AbstractController
{
    public function __construct(
        private AuthService $authService,
        private ValidatorInterface $validator
    ) {}

    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                return $this->json(['error' => 'JSON inválido'], 400);
            }

            $dto = new LoginRequest(
                (string) ($data['email'] ?? ''),
                (string) ($data['password'] ?? '')
            );

            // Validar DTO
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                $messages = [];
                foreach ($errors as $violation) {
                    $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
                }

                return $this->json(['errors' => $messages], 400);
            }

            // Intentar login vía servicio (devuelve token si OK)
            $token = $this->authService->loginUser($dto->getEmail(), $dto->getPassword());

            return $this->json(['token' => $token], 200);

        } catch (InvalidCredentialsException $e) {
            // No damos detalles de si email o password son incorrectos por seguridad
            return $this->json(['error' => 'Credenciales inválidas'], 401);

        } catch (\Throwable $e) {
            // Loguea el error si quieres (logger); devolvemos mensaje genérico
            // $this->logger?->error($e->getMessage(), ['exception' => $e]);
            return $this->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
