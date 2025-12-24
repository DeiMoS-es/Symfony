<?php

namespace App\Module\Group\Repository;

use App\Module\Group\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GroupRepository extends ServiceEntityRepository{

    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Group::class);
    }

    public function findByName(string $name): ?Group{
        return $this->findOneBy(['name' => $name]);
    }

    public function save(Group $group): void{
        $em = $this->getEntityManager();
        $em->persist($group);
        $em->flush();
    }

    
    public function delete(Group $group): void{
        $em = $this->getEntityManager();
        $group->setActive(false);
        $em->flush();
    }

    
    public function isActive(Group $group): bool{
        return $group->isActive();
    }
}