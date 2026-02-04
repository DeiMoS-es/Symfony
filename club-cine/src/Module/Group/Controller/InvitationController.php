<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Service\InvitationService;
use App\Module\Auth\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class InvitationController extends AbstractController
{
    /**
     * Envía una invitación a un grupo por email (Desde el panel del grupo)
     */
    #[Route('/group/{id}/invite', name: 'app_group_invite', methods: ['POST'])]
    public function invite(string $id,  Request $request,  InvitationService $invitationService,  LoggerInterface $logger): Response
    {

        $email = $request->request->get('email');

        if (!$email) {
            $this->addFlash('error', 'El email es obligatorio.');
            return $this->redirectToRoute('app_group_show', ['id' => $id]);
        }

        try {
            $invitationService->sendInvitation($email, $id);
            $this->addFlash('success', "Invitación enviada correctamente a {$email}");
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', "Validación: {$e->getMessage()}");
        } catch (\Exception $e) {
            $logger->error('Error al enviar invitación', [
                'email' => $email,
                'groupId' => $id,
                'exception' => $e->getMessage()
            ]);
            $this->addFlash('error', 'No se pudo enviar el correo. Revisa la configuración del servidor de email.');
        }

        return $this->redirectToRoute('app_group_show', ['id' => $id]);
    }

    /**
     * Punto de entrada cuando el usuario pincha el link del email
     */
    #[Route('/join/group/{token}', name: 'app_group_accept_invitation', methods: ['GET'])]
    public function acceptInvitation(string $token, InvitationService $invitationService): Response
    {
        // 1. Validar el token a través del servicio
        $invitation = $invitationService->getValidInvitation($token);

        if (!$invitation) {
            $this->addFlash('error', 'La invitación no es válida, ha expirado o ya fue utilizada.');
            return $this->redirectToRoute('app_dashboard');
        }

        /** @var User|null $user */
        $user = $this->getUser();

        // 2. Si el usuario no está logueado, preparamos la sesión y al registro
        if (!$user) {
            $invitationService->prepareSessionForRegistration($invitation);
            $this->addFlash('info', 'Para unirte al grupo, primero debes registrarte o iniciar sesión.');

            return $this->redirectToRoute('auth_register', [
                'email' => $invitation->getEmail() // Pasamos el email para pre-rellenar el formulario
            ]);
        }

        // 3. Si ya está logueado, intentamos procesar la unión directamente
        try {
            $group = $invitationService->acceptInvitation($invitation, $user);
            $this->addFlash('success', "¡Felicidades! Ya eres miembro del grupo: {$group->getName()}");

            return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
        } catch (\LogicException $e) {
            // El servicio lanza esto si el email del logueado no coincide con el invitado
            $this->addFlash('warning', $e->getMessage());
            return $this->redirectToRoute('app_dashboard');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ocurrió un error inesperado al unirte al grupo.');
            return $this->redirectToRoute('app_dashboard');
        }
    }
}
