<?php

namespace App\Module\Group\Service;

use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * InvitationService
 * * Maneja el ciclo completo de invitaciones:
 * - Crear y enviar invitaciones
 * - Validar tokens
 * - Preparar sesión para nuevos registros
 * - Aceptar invitaciones y limpiar sesión
 */
class InvitationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack
    ) {}

    /**
     * Crea una invitación y envía el email
     */
    public function sendInvitation(string $email, string $groupId): void
    {
        $this->validateEmail($email);

        $group = $this->em->getRepository(Group::class)->find($groupId);
        if (!$group) {
            throw new \InvalidArgumentException("El grupo con ID {$groupId} no existe.");
        }

        $invitation = new GroupInvitation($email, $group);

        $this->em->persist($invitation);
        $this->em->flush();

        $this->sendInvitationEmail($invitation);
    }

    /**
     * Valida un token y maneja la expiración automáticamente
     */
    public function getValidInvitation(string $token): ?GroupInvitation
    {
        $invitation = $this->em->getRepository(GroupInvitation::class)->findOneBy(['token' => $token]);

        if (!$invitation) {
            return null;
        }

        if ($invitation->isExpired()) {
            $this->em->remove($invitation);
            $this->em->flush();
            return null;
        }

        return $invitation;
    }

    /**
     * Guarda el token en sesión para que el RegistrationController lo use tras el registro
     */
    public function prepareSessionForRegistration(GroupInvitation $invitation): void
    {
        $session = $this->requestStack->getSession();
        $session->set('pending_invitation_token', $invitation->getToken());
        $session->set('pending_invitation_email', $invitation->getEmail());
    }

    /**
     * Une al usuario al grupo, valida el email y limpia la sesión
     */
    public function acceptInvitation(GroupInvitation $invitation, User $user): Group
    {
        // Seguridad: El email del usuario logueado debe coincidir con el de la invitación
        if ($user->getEmail() !== $invitation->getEmail()) {
            throw new \LogicException("Este enlace de invitación pertenece a otra cuenta de correo.");
        }

        $group = $invitation->getTargetGroup();

        // Añadimos el usuario al grupo (Group::addUser debería manejar la relación)
        $group->addUser($user);

        // Borramos la invitación (ya se ha usado)
        $this->em->remove($invitation);
        $this->em->flush();

        // Limpiamos la sesión
        $session = $this->requestStack->getSession();
        $session->remove('pending_invitation_token');
        $session->remove('pending_invitation_email');

        return $group;
    }

    private function sendInvitationEmail(GroupInvitation $invitation): void
    {
        $acceptUrl = $this->urlGenerator->generate(
            'app_group_accept_invitation',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new TemplatedEmail())
            ->from('contacto.cineapp@gmail.com')
            ->to($invitation->getEmail())
            ->subject("¡Te han invitado a unirte a {$invitation->getTargetGroup()->getName()}!")
            ->htmlTemplate('emails/group_invitation.html.twig')
            ->context([
                'acceptUrl' => $acceptUrl,
                'groupName' => $invitation->getTargetGroup()->getName(),
                'expiresAt' => $invitation->getExpiresAt()
            ]);

        $this->mailer->send($emailMessage);
    }

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("El email '{$email}' no es válido.");
        }
    }

    /**
     * Determina la ruta de redirección tras el registro exitoso
     */
    public function getAfterRegistrationRedirectRoute(): array
    {
        $session = $this->requestStack->getSession();
        $pendingToken = $session->get('pending_invitation_token');

        if ($pendingToken) {
            return [
                'route' => 'app_group_accept_invitation',
                'params' => ['token' => $pendingToken]
            ];
        }

        return [
            'route' => 'app_dashboard',
            'params' => []
        ];
    }
}
