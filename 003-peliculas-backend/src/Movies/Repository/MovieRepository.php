<?php

namespace App\Movies\Repository;

use App\Movies\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
     * Encuentra las películas más populares.
     */
    public function findMostPopular(int $limit = 10): array
    {

        return $this->createQueryBuilder('m')
            ->orderBy('m.popularity', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    /**
     * Encuentra película por el título
     */
    public function findByTitle(string $title): array
    {

        return $this->createQueryBuilder('m')
            ->where('m.title_movie LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->orderBy('m.release_date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithGenres(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.genres', 'g')
            ->addSelect('g') // Carga los géneros en la misma consulta
            ->getQuery()
            ->getResult();
    }

    public function save(Movie $movie, bool $flush = false): void
    {
        $em = $this->getEntityManager(); // Usa el método correcto
        $em->persist($movie);
        if ($flush) {
            $em->flush();
        }
    }

    public function remove(Movie $movie, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($movie);
        if ($flush) {
            $em->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
