<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\DTO\UserResponse;
use App\Module\Auth\Service\RegistrationService;
use App\Module\Auth\Exception\UserAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        RegistrationService $registrationService,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->registrationService = $registrationService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            // 1️⃣ Convertir JSON a DTO
            /** @var RegistrationRequest $registrationRequest */
            $registrationRequest = $this->serializer->deserialize(
                $request->getContent(),
                RegistrationRequest::class,
                'json'
            );

            // 2️⃣ Llamar al service
            $userResponse = $this->registrationService->register($registrationRequest);

            // 3️⃣ Devolver JSON con datos seguros
            return $this->json($userResponse, 201);
        } catch (UserAlreadyExistsException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => 'Datos inválidos', 'details' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
