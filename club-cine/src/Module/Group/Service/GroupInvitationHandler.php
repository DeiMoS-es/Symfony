<?php
namespace App\Module\Group\Service;

use App\Module\Group\Entity\GroupInvitation;
use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;

class GroupInvitationHandler
{
    public function __construct(private EntityManagerInterface $em) {}

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

    public function handleAcceptance(GroupInvitation $invitation, User $user): Group
    {
        $group = $invitation->getTargetGroup();

        if (!$group->getUsers()->contains($user)) {
            $group->addUser($user);
        }

        $this->em->remove($invitation);
        $this->em->flush();

        return $group;
    }
}
?>