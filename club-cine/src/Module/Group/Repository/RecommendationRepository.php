<?php

namespace App\Module\Group\Repository;

use App\Module\Group\Entity\Recommendation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecommendationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recommendation::class);
    }

    public function save(Recommendation $recommendation): void
    {
        $this->getEntityManager()->persist($recommendation);
        $this->getEntityManager()->flush();
    }

    /**
     * Devuelve las recomendaciones abiertas para un grupo específico
     */
    public function findActiveByGroup($group): array
    {
        return $this->findBy([
            'group' => $group,
            'status' => 'OPEN'
        ], ['createdAt' => 'DESC']);
    }

    /**
     * Busca recomendaciones que han superado la fecha límite y siguen abiertas
     */
    public function findExpiredToClose(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.status = :status')
            ->andWhere('r.deadline <= :now')
            ->setParameter('status', 'OPEN')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
?>