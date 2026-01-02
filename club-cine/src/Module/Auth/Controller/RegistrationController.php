<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\Service\RegistrationService;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Auth\Mapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;
    private ValidatorInterface $validator;

    public function __construct(
        RegistrationService $registrationService,
        ValidatorInterface $validator
    ) {
        $this->registrationService = $registrationService;
        $this->validator = $validator;
    }

    #[Route('/register/{token}', name: 'auth_register', methods: ['GET', 'POST'], defaults: ['token' => null])]
    public function register(Request $request, EntityManagerInterface $em, ?string $token = null): Response
    {
        // 1. El servicio decide qué email mostrar (GET y fallos de POST)
        $invitationEmail = $this->registrationService->getInvitationEmail($token);

        if ($request->isMethod('GET')) {
            return $this->render('auth/register.html.twig', [
                'errors' => [],
                'email' => $invitationEmail,
                'name' => '',
            ]);
        }

        // 2. Validación CSRF
        if (!$this->isCsrfTokenValid('auth_register', $request->request->get('_csrf_token'))) {
            return $this->renderError(['Token de seguridad inválido.'], $request->request->all());
        }

        // 3. Mapeo y validación de reglas de UI
        $regRequest = UserMapper::fromRequest($request);
        $errors = $this->validateBusinessRules($regRequest, $request->request->get('confirm_password'));

        if (count($errors) > 0) {
            return $this->renderError($errors, $request->request->all());
        }

        // 4. Ejecución
        try {
            $this->registrationService->register($regRequest, $token);
            $this->addFlash('success', '¡Registro completado!');
            return $this->redirectToRoute('auth_login');
        } catch (\Exception $e) {
            // El servicio lanza excepciones con mensajes claros que podemos mostrar
            return $this->renderError([$e->getMessage()], $request->request->all());
        }
    }

    /**
     * Valida reglas que son exclusivas de la interfaz de usuario (UI)
     */
    private function validateBusinessRules(RegistrationRequest $dto, ?string $confirmPassword): array
    {
        $errors = [];

        // Validaciones del DTO (Email válido, longitud nombre, etc.)
        foreach ($this->validator->validate($dto) as $violation) {
            $errors[] = $violation->getMessage();
        }

        // Validación de coincidencia (esto no suele estar en el DTO)
        if ($dto->plainPassword !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        return $errors;
    }

    private function renderError(array $errors, array $data): Response
    {
        return $this->render('auth/register.html.twig', [
            'errors' => $errors,
            'email' => $data['email'] ?? '',
            'name' => $data['name'] ?? '',
        ]);
    }
}
