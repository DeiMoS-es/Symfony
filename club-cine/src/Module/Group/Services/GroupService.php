<?php

namespace App\Module\Group\Services;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Group\Entity\GroupMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GroupService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private Security $security
    ) {}

    public function createGroup(string $name, User $owner, ?string $description = null): Group
    {
        $group = new Group($name, $owner, $description);

        $this->em->persist($group);
        $this->em->flush();

        // IMPORTANTE: Refrescar la entidad User para que sus colecciones 
        // de "members" y "groups" incluyan el nuevo registro de inmediato.
        $this->em->refresh($owner);

        return $group;
    }

    public function processInvitation(Group $group, string $email): string
    {
        $userRepo = $this->em->getRepository(User::class);
        $existingUser = $userRepo->findOneBy(['email' => $email]);

        if ($existingUser) {
            // Lógica: Si ya existe, lo intentamos añadir directamente
            if ($this->isMember($group, $existingUser)) {
                return 'warning|Esta persona ya es miembro del club.';
            }

            $newMember = new GroupMember($group, $existingUser, 'MEMBER');
            $this->em->persist($newMember);
            $this->em->flush();
            return "success|{$existingUser->getName()} ha sido añadido directamente.";
        }

        // Si no existe, creamos invitación y enviamos email
        $invitation = new GroupInvitation($email, $group, new \DateTimeImmutable('+7 days'));
        $this->em->persist($invitation);

        $emailMsg = (new TemplatedEmail())
            ->from('clubdecine@tuapp.com')
            ->to($email)
            ->subject("Invitación al club: " . $group->getName())
            ->htmlTemplate('emails/invitation.html.twig')
            ->context(['groupName' => $group->getName(), 'token' => $invitation->getToken()]);

        $this->mailer->send($emailMsg);
        $this->em->flush();

        return "success|Invitación enviada a $email.";
    }

    private function isMember(Group $group, User $user): bool
    {
        return $group->getMembers()->exists(fn($key, $m) => $m->getUser() === $user);
    }

    /**
     * Elimina un grupo y toda su actividad asociada.
     * Solo permite la acción si el usuario es el OWNER.
     */
    public function deleteGroup(Group $group): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // 1. Validación de Regla de Negocio
        if ($group->getOwner()->getId()->toString() !== $user->getId()->toString()) {
            throw new AccessDeniedHttpException('Solo el fundador puede eliminar este club.');
        }

        // 2. Ejecución
        $this->em->remove($group);
        $this->em->flush();
    }

    /**
     * Abandonar grupo
     */
    public function leaveGroup(Group $group): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        // 1. Buscamos al miembro usando tu lógica de filtro (que es buena)
        $member = $group->getMembers()->filter(
            fn($m) => $m->getUser()->getId()->toString() === $user->getId()->toString()
        )->first();

        if (!$member) {
            // Opcional: lanzar una excepción si intentan abandonar un sitio donde no están
            throw new \LogicException("No eres miembro de este grupo.");
        }

        // 2. LÓGICA DE SUCESIÓN: ¿Es el dueño el que se va?
        if ($group->getOwner() === $user) {
            // Buscamos al siguiente candidato (el que no sea el actual)
            $successor = $group->getMembers()->filter(fn($m) => $m !== $member)->first();

            if ($successor) {
                // Transferimos la propiedad del club
                $group->setOwner($successor->getUser());
                $successor->setRole('OWNER');
                $this->em->persist($group);
                $this->em->persist($successor);
            } else {
                // Si no hay nadie más, el club se disuelve
                $this->em->remove($group);
                $this->em->flush();
                return; // Salimos, ya no hay más que borrar
            }
        }

        // 3. Borramos el vínculo del miembro
        $this->em->remove($member);
        $this->em->flush();

        // 4. Limpieza de estado (importante para que el selector se actualice)
        $this->em->refresh($user);
    }
}
