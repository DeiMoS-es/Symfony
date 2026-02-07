<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Service\InvitationService;
use App\Module\Auth\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class InvitationController extends AbstractController
{
    public function __construct(
        private InvitationService $invitationService,
        private UserRepository $userRepository
    ) {}

    /**
     * Envia la invitación (POST desde el formulario del grupo)
     */
    #[Route('/group/{id}/invite', name: 'app_group_invite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function invite(Request $request, string $id): Response
    {
        $email = $request->request->get('email');

        try {
            $this->invitationService->processInvitation($email, $id);
            $this->addFlash('success', "Invitación enviada a $email.");
        } catch (\InvalidArgumentException | \LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_group_show', ['id' => $id]);
    }

    /**
     * Punto de entrada cuando el usuario pulsa el botón del email
     */
    #[Route('/invitation/accept/{token}', name: 'app_group_accept_invitation', methods: ['GET'])]
    public function accept(string $token): Response
    {
        $invitation = $this->invitationService->getValidInvitation($token);

        if (!$invitation) {
            $this->addFlash('error', 'La invitación ha expirado o no es válida.');
            return $this->redirectToRoute('user_dashboard');
        }

        // Caso 1: El usuario ya tiene la sesión iniciada
        if ($this->getUser()) {
            try {
                $group = $this->invitationService->acceptInvitation($invitation, $this->getUser());
                $this->addFlash('success', "¡Bienvenido al grupo {$group->getName()}!");
                return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
            } catch (\LogicException $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('user_dashboard');
            }
        }

        // Caso 2: No está logueado. Preparamos la sesión.
        $this->invitationService->prepareSessionForRegistration($invitation);

        // ¿El usuario ya tiene cuenta en la app?
        $userExists = $this->userRepository->findOneBy(['email' => $invitation->getEmail()]);

        if ($userExists) {
            $this->addFlash('info', 'Inicia sesión para unirte al grupo.');
            return $this->redirectToRoute('auth_login'); // Usa el nombre de tu ruta de login
        }

        $this->addFlash('info', 'Crea una cuenta para unirte al grupo.');
        return $this->redirectToRoute('auth_register'); // Usa el nombre de tu ruta de registro
    }
}