<?php
namespace App\Module\Movie\Repository;

use App\Module\Movie\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    public function findByName(string $name): ?Genre
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(Genre $genre, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($genre);
        if ($flush) {
            $em->flush();
        }
    }

    public function remove(Genre $genre, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($genre);
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Devuelve todos los géneros ordenados alfabéticamente.
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
