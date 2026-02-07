<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\Service\RegistrationService;
use App\Module\Auth\Mapper\UserMapper;
use App\Module\Auth\Entity\User;
use App\Module\Group\Service\InvitationService;
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
        private ValidatorInterface $validator,
        private InvitationService $invitationService
    ) {}

    #[Route('/register', name: 'auth_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $session = $request->getSession();
        $emailFromInvite = $session->get('pending_invitation_email');
        // NUEVO: Bandera booleana para saber si hay invitación activa
        $isInvitation = (bool)$session->get('pending_invitation_token');

        if ($request->isMethod('GET')) {
            return $this->render('auth/register.html.twig', [
                'errors' => [],
                'email' => $emailFromInvite,
                'name' => '',
                'is_invitation' => $isInvitation, // <--- ENVIAR AQUÍ
            ]);
        }

        if (!$this->isCsrfTokenValid('auth_register', $request->request->get('_csrf_token'))) {
            return $this->renderError(['Token de seguridad inválido.'], $request->request->all(), $isInvitation);
        }

        $regRequest = UserMapper::fromRequest($request);
        $errors = $this->validateBusinessRules($regRequest, $request->request->get('confirm_password'));

        if (count($errors) > 0) {
            return $this->renderError($errors, $request->request->all(), $isInvitation);
        }

        try {
            $userResponse = $this->registrationService->register($regRequest);
            $user = $em->getRepository(User::class)->findOneBy(['email' => $userResponse->email]);

            if (!$user) {
                throw new \Exception("Error al recuperar el usuario creado.");
            }

            $security->login($user, 'form_login', 'main');

            $redirectData = $this->invitationService->getAfterRegistrationRedirectRoute();
            $this->addFlash('success', '¡Registro completado y bienvenido!');

            return $this->redirectToRoute($redirectData['route'], $redirectData['params']);
        } catch (\Exception $e) {
            return $this->renderError([$e->getMessage()], $request->request->all(), $isInvitation);
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

    private function renderError(array $errors, array $data, bool $isInvitation): Response
    {
        return $this->render('auth/register.html.twig', [
            'errors' => $errors,
            'email' => $data['email'] ?? '',
            'name' => $data['name'] ?? '',
            'is_invitation' => $isInvitation
        ]);
    }
}
