<?php
namespace App\Module\Movie\Repository;

use App\Module\Movie\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;

class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function findByTmdbId(int $tmdbId): ?Movie
    {
        return $this->findOneBy(['tmdbId' => $tmdbId]);
    }

    public function findByUuid(string $uuid): ?Movie
    {
        return $this->find(Uuid::fromString($uuid));
    }

    public function save(Movie $movie, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($movie);
        if ($flush) {
            $em->flush();
        }
    }

    public function remove(Movie $movie, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($movie);
        if ($flush) {
            $em->flush();
        }
    }

    public function searchByTitle(string $term, int $limit = 20, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.genres', 'g')
            ->addSelect('g')
            ->where('LOWER(m.title) LIKE :term')
            ->setParameter('term', '%'.strtolower($term).'%')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('m.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
