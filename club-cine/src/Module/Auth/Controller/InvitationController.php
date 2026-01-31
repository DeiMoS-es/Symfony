<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\Entity\GroupInvitation;
use App\Module\Auth\Entity\User;
use App\Module\Notification\Service\EmailService;
use App\Module\Group\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvitationController extends AbstractController
{
    #[Route('/group/{id}/invite', name: 'app_group_invite', methods: ['POST'])]
    public function invite( Group $group, Request $request, EntityManagerInterface $em, EmailService $emailService): Response {
        $email = $request->request->get('email');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'El email introducido no es válido.');
            return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
        }

        // 1. Crear y guardar invitación
        $invitation = new GroupInvitation($email, $group);
        $em->persist($invitation);
        $em->flush();

        // 2. Enviar Email
        $emailService->sendGroupInvitation($email, $group->getName(), $invitation->getToken());

        // 3. Notificar al usuario y redirigir
        $this->addFlash('success', "¡Invitación enviada a $email!");

        // Suponiendo que tu ruta de detalle de grupo se llama 'app_group_show'
        return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
    }

    #[Route('/join/group/{token}', name: 'app_group_accept_invitation', methods: ['GET'])]
    public function acceptInvitation(string $token, EntityManagerInterface $em): Response
    {
        $invitation = $em->getRepository(GroupInvitation::class)->findOneBy(['token' => $token]);

        if (!$invitation) {
            throw $this->createNotFoundException('Esta invitación no existe o ya ha sido utilizada.');
        }

        if ($invitation->isExpired()) {
            $em->remove($invitation);
            $em->flush();
            return new Response("La invitación ha caducado.");
        }

        $group = $invitation->getTargetGroup();
        /** @var ?User $user */
        $user = $this->getUser();

        if (!$user) {
            // Guardamos el token en la sesión para volver aquí tras el login
            return $this->redirectToRoute('app_login', ['_target_path' => $this->generateUrl('app_group_accept_invitation', ['token' => $token])]);
        }

        // Usamos tu lógica de la entidad Group (que ya maneja GroupMember internamente)
        // Comparamos los IDs usando la colección de usuarios que genera tu método getUsers()
        $isAlreadyMember = false;
        foreach ($group->getUsers() as $memberUser) {
            if ($memberUser->getId()->toString() === $user->getId()->toString()) {
                $isAlreadyMember = true;
                break;
            }
        }

        if (!$isAlreadyMember) {
            $group->addUser($user);
            $em->flush();
        }

        $em->remove($invitation);
        $em->flush();

        // Asegúrate de tener esta vista creada o cambia la ruta
        return $this->render('group/welcome.html.twig', [
            'group' => $group
        ]);
    }
}
