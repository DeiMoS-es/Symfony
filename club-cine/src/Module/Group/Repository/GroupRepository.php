<?php

namespace App\Module\Group\Repository;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GroupRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    public function findByName(string $name): ?Group
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(Group $group): void
    {
        $em = $this->getEntityManager();
        $em->persist($group);
        $em->flush();
    }


    public function delete(Group $group): void
    {
        $em = $this->getEntityManager();
        $group->setActive(false);
        $em->flush();
    }


    public function isActive(Group $group): bool
    {
        return $group->isActive();
    }

    public function findGroupsByUser(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.members', 'm') // Unimos con la tabla intermedia GroupMember
            ->where('g.owner = :user')    // Es el creador
            ->orWhere('m.user = :user')   // O es un invitado aceptado
            ->setParameter('user', $user)
            ->distinct()                  // Evitamos duplicados
            ->getQuery()
            ->getResult();
    }
}
