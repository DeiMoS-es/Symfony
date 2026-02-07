<?php

namespace App\Module\Group\Service;

use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\GroupMember;
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
     * Procesa la lógica de invitación con validaciones
     */
    public function processInvitation(string $email, string $groupId): void
    {
        $group = $this->em->getRepository(Group::class)->find($groupId);
        if (!$group) {
            throw new \InvalidArgumentException("El grupo aún no existe.");
        }

        // 1. Comprobación: ¿Ya eres miembro del grupo?
        if ($this->isUserAlreadyMember($email, $group)) {
            throw new \LogicException("El usuario con email {$email} ya es miembro del grupo.");
        }

        // 2. Comprobación: ¿Ya existe una invitación pendiente para este email y grupo?
        if ($this->hasPendingInvitation($email, $group)) {
            throw new \LogicException("Ya existe una invitación pendiente para {$email} en este grupo.");
        }

        // 3. Detección: ¿El usuario existe en la plataforma?
        $userExists = $this->em->getRepository(User::class)->findOneBy(['email' => $email]) !== null;

        // 4. Creación: Persistir la invitación
        $invitation = new GroupInvitation($email, $group);
        $this->em->persist($invitation);
        $this->em->flush();

        // 5. Envío: Mandar correo personalizado
        $this->sendInvitationEmail($invitation, (bool)$userExists);
    }

    private function isUserAlreadyMember(string $email, Group $group): bool
    {
        // Buscamos si existe un GroupMember vinculado a ese email en este grupo
        return (bool) $this->em->getRepository(GroupMember::class)
            ->createQueryBuilder('gm')
            ->join('gm.user', 'u')
            ->where('u.email = :email')
            ->andWhere('gm.group = :group')
            ->setParameter('email', $email)
            ->setParameter('group', $group)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function hasPendingInvitation(string $email, Group $group): bool
    {
        $existing = $this->em->getRepository(GroupInvitation::class)->findOneBy([
            'email' => $email,
            'targetGroup' => $group
        ]);

        return $existing && !$existing->isExpired();
    }

    /**
     * Crea una invitación y envía el email
     */
    public function sendInvitationEmail(GroupInvitation $invitation, bool $isRegisteredUser): void
    {
        $acceptUrl = $this->urlGenerator->generate(
            'app_group_accept_invitation',
            ['token' => $invitation->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new TemplatedEmail())
            ->from('contacto.cineapp@gmail.com')
            ->to($invitation->getEmail())
            // Personalizamos el asunto según el estado
            ->subject($isRegisteredUser
                ? "¡Te han invitado a unirte a {$invitation->getTargetGroup()->getName()}!"
                : "¡Únete a {$invitation->getTargetGroup()->getName()} en CineApp!")
            ->htmlTemplate('emails/group_invitation.html.twig')
            ->context([
                'acceptUrl' => $acceptUrl,
                'groupName' => $invitation->getTargetGroup()->getName(),
                'expiresAt' => $invitation->getExpiresAt(),
                'isRegisteredUser' => $isRegisteredUser
            ]);

        $this->mailer->send($emailMessage);
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
        if ($user->getEmail() !== $invitation->getEmail()) {
            throw new \LogicException("Este enlace de invitación pertenece a otra cuenta de correo.");
        }

        $group = $invitation->getTargetGroup();

        // 1. Creamos el miembro explícitamente (el "pegamento")
        // Pasamos el grupo, el usuario y el rol al constructor
        $groupMember = new GroupMember($group, $user, 'MEMBER');

        // 2. Persistimos el miembro DIRECTAMENTE
        // Esto garantiza que Doctrine genere un INSERT en la tabla app_group_member
        $this->em->persist($groupMember);

        // 3. Opcional: añadimos a la colección en memoria para la respuesta inmediata
        $group->getMembers()->add($groupMember);

        // 4. Limpiamos invitación y guardamos todo
        $this->em->remove($invitation);
        $this->em->flush();

        // 5. Limpiamos sesión
        $session = $this->requestStack->getSession();
        $session->remove('pending_invitation_token');
        $session->remove('pending_invitation_email');

        return $group;
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
            'route' => 'user_dashboard',
            'params' => []
        ];
    }
}
