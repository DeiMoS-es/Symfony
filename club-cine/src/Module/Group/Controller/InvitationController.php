<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Service\InvitationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    /**
     * Envía una invitación a un grupo por email
     * Ruta: POST /group/{id}/invite
     */
    #[Route('/group/{id}/invite', name: 'app_group_invite', methods: ['POST'])]
    public function invite(string $id, Request $request, InvitationService $invitationService, \Psr\Log\LoggerInterface $logger): Response
    {
        $email = $request->request->get('email');

        if (!$email) {
            $this->addFlash('error', 'El email es obligatorio.');
            return $this->redirectToRoute('app_group_show', ['id' => $id]);
        }

        try {
            $invitationService->sendInvitation($email, $id);
            $this->addFlash('success', 'Invitación enviada correctamente a ' . $email);
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', 'Validación: ' . $e->getMessage());
        } catch (\Exception $e) {
            // dd($e->getMessage());
            // Registrar error completo para poder inspeccionarlo en logs
            $logger->error('Error al enviar invitación', ['email' => $email, 'groupId' => $id, 'exception' => $e]);
            $this->addFlash('error', 'Error al enviar la invitación. Intenta más tarde.');
        }

        return $this->redirectToRoute('app_group_show', ['id' => $id]);
    }

    /**
     * Acepta una invitación a un grupo
     * Ruta: GET /join/group/{token}
     * 
     * Flujo:
     * 1. Valida el token de invitación
     * 2. Si no hay usuario logueado, redirige a registro con el email
     * 3. Si hay usuario, lo agrega al grupo
     * 4. Muestra la página de bienvenida
     */
    #[Route('/join/group/{token}', name: 'app_group_accept_invitation', methods: ['GET'])]
    public function acceptInvitation(string $token, InvitationService $invitationService): Response
    {
        // Validar que la invitación existe y no ha expirado
        $invitation = $invitationService->getValidInvitation($token);

        if (!$invitation) {
            $this->addFlash('error', 'La invitación no existe, ha expirado o ya ha sido utilizada.');
            return $this->redirectToRoute('app_home');
        }

        // Verificar si hay usuario autenticado
        $user = $this->getUser();
        if (!$user) {
            // Redirigir a registro pasando el token para que recupere el email de la invitación
            return $this->redirectToRoute('auth_register', [
                'token' => $token,
                '_target_path' => $this->generateUrl('app_group_accept_invitation', ['token' => $token])
            ]);
        }

        // Usuario autenticado: aceptar la invitación
        try {
            $group = $invitationService->acceptInvitation($invitation, $user);
            $this->addFlash('success', "¡Felicidades! Te has unido al grupo: {$group->getName()}");
            
            return $this->render('group/welcome.html.twig', ['group' => $group]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al procesar tu unión al grupo. Intenta más tarde.');
            return $this->redirectToRoute('app_home');
        }
    }
}