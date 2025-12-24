<?php

namespace App\Module\Group\Repository;

use App\Module\Group\Entity\GroupMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GroupMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupMember::class);
    }

    public function save(GroupMember $member): void
    {
        $this->getEntityManager()->persist($member);
        $this->getEntityManager()->flush();
    }

    public function findByUserAndGroup($user, $group): ?GroupMember
    {
        return $this->findOneBy([
            'user' => $user,
            'group' => $group
        ]);
    }
}

?>