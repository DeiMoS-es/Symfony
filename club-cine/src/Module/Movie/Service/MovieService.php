<?php

namespace App\Module\Movie\Service;

use App\Module\Movie\DTO\MovieUpsertRequest;
use App\Module\Movie\Entity\Movie;
use App\Module\Movie\Entity\Genre;
use App\Module\Movie\Repository\MovieRepository;
use App\Module\Movie\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Servicio principal para la lógica de negocio de Películas.
 */
class MovieService
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
        private readonly GenreRepository $genreRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * Busca una película por su TmdbId o crea una nueva si no existe,
     * y luego la hidrata con los datos del DTO.
     */
    public function findOrCreateFromUpsertDTO(MovieUpsertRequest $dto): Movie
    {
        // 1. Buscar o crear la Película
        $movie = $this->movieRepository->findOneBy(['tmdbId' => $dto->tmdbId]);
        if (!$movie) {
            $movie = new Movie($dto->tmdbId, $dto->title);
            $this->em->persist($movie);
        }

        // 2. Mapear propiedades simples
        $movie->setTitle($dto->title);
        $movie->setOverview($dto->overview);
        $movie->setPosterPath($dto->posterPath);
        $movie->setReleaseDate($dto->releaseDate);

        // 3. Mapear géneros
        $this->mapGenresToMovie($movie, $dto->genres);

        return $movie;
    }

    /**
     * Lógica de mapeo para los géneros.
     *
     * @param array<int|string>|null $genreIdentifiers
     */
    private function mapGenresToMovie(Movie $movie, ?array $genreIdentifiers): void
    {
        if ($genreIdentifiers === null) {
            return;
        }

        // Limpiamos géneros actuales de forma segura
        foreach ($movie->getGenres() as $existingGenre) {
            $movie->removeGenre($existingGenre);
        }

        foreach ($genreIdentifiers as $identifier) {
            $genre = $this->findOrCreateGenre($identifier);
            if ($genre) {
                $movie->addGenre($genre);
            }
        }
    }

    /**
     * Busca o crea un género según el identificador.
     *
     * @param int|string $identifier
     */
    private function findOrCreateGenre(int|string $identifier): ?Genre
    {
        $genre = null;

        if (is_int($identifier)) {
            // Si tu entidad Genre tiene tmdbId, úsalo aquí
            $genre = $this->genreRepository->findOneBy(['tmdbId' => $identifier]);
        }

        if (is_string($identifier)) {
            $genre = $this->genreRepository->findOneBy(['name' => $identifier]);
            if (!$genre) {
                $genre = new Genre($identifier);
                $this->em->persist($genre);
            }
        }

        return $genre;
    }
}
