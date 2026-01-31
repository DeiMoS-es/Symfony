<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\Service\RegistrationService;
use App\Module\Auth\Mapper\UserMapper;
use App\Module\Auth\Entity\User; // <--- ESTO corrige el error "Undefined type"
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class RegistrationController extends AbstractController
{
    public function __construct(
        private RegistrationService $registrationService,
        private ValidatorInterface $validator
    ) {}

    #[Route('/register/{token}', name: 'auth_register', methods: ['GET', 'POST'], defaults: ['token' => null])]
    public function register(
        Request $request, 
        EntityManagerInterface $em, // <--- Necesario para buscar al usuario después
        Security $security, 
        ?string $token = null
    ): Response {
        
        $invitationEmail = $this->registrationService->getInvitationEmail($token);

        if ($request->isMethod('GET')) {
            return $this->render('auth/register.html.twig', [
                'errors' => [],
                'email' => $invitationEmail,
                'name' => '',
            ]);
        }

        if (!$this->isCsrfTokenValid('auth_register', $request->request->get('_csrf_token'))) {
            return $this->renderError(['Token de seguridad inválido.'], $request->request->all());
        }

        $regRequest = UserMapper::fromRequest($request);
        $errors = $this->validateBusinessRules($regRequest, $request->request->get('confirm_password'));

        if (count($errors) > 0) {
            return $this->renderError($errors, $request->request->all());
        }

        try {
            // 1. Registramos al usuario
            $userResponse = $this->registrationService->register($regRequest);
            
            // 2. Buscamos la entidad real para loguearla
            $user = $em->getRepository(User::class)->findOneBy(['email' => $userResponse->email]);

            if (!$user) {
                throw new \Exception("Error al recuperar el usuario creado.");
            }

            // 3. Login automático
            $security->login($user, 'form_login', 'main');

            // 4. Redirección si hay invitación pendiente
            $targetPath = $request->query->get('_target_path');
            if ($targetPath) {
                return $this->redirect($targetPath);
            }

            $this->addFlash('success', '¡Registro completado!');
            return $this->redirectToRoute('user_dashboard');

        } catch (\Exception $e) {
            return $this->renderError([$e->getMessage()], $request->request->all());
        }
    }

    private function validateBusinessRules(RegistrationRequest $dto, ?string $confirmPassword): array
    {
        $errors = [];
        foreach ($this->validator->validate($dto) as $violation) {
            $errors[] = $violation->getMessage();
        }
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