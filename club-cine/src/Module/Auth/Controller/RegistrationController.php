<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\DTO\UserResponse;
use App\Module\Auth\Service\RegistrationService;
use App\Module\Auth\Exception\UserAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/register', name: 'auth_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('auth/register.html.twig', [
                'errors' => [],
                'email' => '',
                'name' => '',
            ]);
        }

        // --- CSRF check ---
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('auth_register', $csrfToken)) {
            return $this->render('auth/register.html.twig', [
                'errors' => ['Token CSRF inválido.'],
                'email' => $request->request->get('email', ''),
                'name' => $request->request->get('name', ''),
            ]);
        }

        $data = $request->request->all();
        $registrationRequest = new RegistrationRequest();
        $registrationRequest->email = $data['email'] ?? '';
        $registrationRequest->name = $data['name'] ?? '';
        $registrationRequest->plainPassword = $data['password'] ?? '';

        // Validación
        $errors = [];
        foreach ($this->validator->validate($registrationRequest) as $violation) {
            $errors[] = $violation->getMessage();
        }

          // --- Validar confirm_password ---
        if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            $errors[] = 'La contraseña y la confirmación no coinciden.';
        }

        if (count($errors) > 0) {
            return $this->render('auth/register.html.twig', [
                'errors' => $errors,
                'email' => $registrationRequest->email,
                'name' => $registrationRequest->name,
            ]);
        }

        try {
            $this->registrationService->register($registrationRequest);

            $this->addFlash('success', 'Usuario registrado correctamente. ¡Ya puedes iniciar sesión!');
            return $this->redirectToRoute('auth_login');
        } catch (UserAlreadyExistsException $e) {
            return $this->render('auth/register.html.twig', [
                'errors' => [$e->getMessage()],
                'email' => $registrationRequest->email,
                'name' => $registrationRequest->name,
            ]);
        } catch (\Throwable $e) {
            return $this->render('auth/register.html.twig', [
                'errors' => ['Error interno del servidor'],
                'email' => $registrationRequest->email,
                'name' => $registrationRequest->name,
            ]);
        }
    }
}
