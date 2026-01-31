<?php

namespace App\Module\Group\Service;

use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * InvitationService
 * 
 * Maneja el ciclo completo de invitaciones a grupos:
 * - Crear y enviar invitaciones
 * - Validar tokens
 * - Aceptar invitaciones y agregar usuarios al grupo
 */
class InvitationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Crea una invitación a un grupo y envía el email
     * 
     * @param string $email    Email del invitado
     * @param string $groupId  ID (UUID) del grupo
     * 
     * @throws \InvalidArgumentException si el grupo no existe o el email es inválido
     * @throws \Exception si hay error al enviar el email
     */
    public function sendInvitation(string $email, string $groupId): void
    {
        $this->validateEmail($email);
        
        $group = $this->em->getRepository(Group::class)->find($groupId);
        if (!$group) {
            throw new \InvalidArgumentException("El grupo con ID {$groupId} no existe.");
        }

        // Crear invitación usando el constructor (que genera token y expiration)
        $invitation = new GroupInvitation($email, $group);

        $this->em->persist($invitation);
        $this->em->flush();

        // Enviar email con link de aceptación
        $this->sendInvitationEmail($invitation);
    }

    /**
     * Obtiene y valida una invitación por token
     * 
     * @return GroupInvitation|null null si no existe o ha expirado
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
     * Acepta una invitación y añade el usuario al grupo
     * 
     * @throws \Exception si hay error al procesar
     */
    public function acceptInvitation(GroupInvitation $invitation, User $user): Group
    {
        $group = $invitation->getTargetGroup();

        // Group::addUser ya evita duplicados internamente
        $group->addUser($user);

        // Eliminar la invitación una vez usada
        $this->em->remove($invitation);
        $this->em->flush();

        return $group;
    }

    /**
     * Envía el email de invitación
     */
    private function sendInvitationEmail(GroupInvitation $invitation): void
    {
        $acceptUrl = $this->urlGenerator->generate(
            'app_group_accept_invitation',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new TemplatedEmail())
            ->from('no-reply@clubdecine.com')
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

    /**
     * Valida que el email sea válido
     * 
     * @throws \InvalidArgumentException
     */
    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("El email '{$email}' no es válido.");
        }
    }
}