<?php
namespace App\Module\Group\Services;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Group\Entity\GroupMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class GroupService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer
    ) {}

    public function createGroup(Group $group, User $user): void
    {
        $this->em->persist($group);
        $user->addGroup($group);
        $this->em->flush();
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
}
?>