<?php

namespace App\Module\Movie\Service;

use App\Module\Movie\DTO\MovieUpsertRequest;
use App\Module\Movie\Entity\Movie;
use App\Module\Movie\Entity\Genre;
use App\Module\Movie\Repository\MovieRepository;
use App\Module\Movie\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Module\Movie\Service\TmdbService;

/**
 * Servicio principal para la lógica de negocio de Películas.
 */
class MovieService
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
        private readonly GenreRepository $genreRepository,
        private readonly EntityManagerInterface $em,
        private readonly TmdbService $tmdbService,
    ) {}

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

    /**
     * Obtiene los detalles de la película desde TMDB y los guarda en nuestra base de datos local.
     */
    public function getAndPersistFromTmdb(int $tmdbId): Movie
    {
        // 1. Si ya existe en nuestra DB, no hacemos nada más
        $movie = $this->movieRepository->findOneBy(['tmdbId' => $tmdbId]);
        if ($movie) {
            return $movie;
        }

        // 2. Obtenemos datos de la API a través de tu TmdbService
        $data = $this->tmdbService->fetchMovie($tmdbId);
        if (!$data) {
            throw new \RuntimeException(sprintf('No se pudo encontrar la película con ID %d en TMDB', $tmdbId));
        }

        // 3. Preparamos los datos complejos (fechas y géneros)
        $releaseDate = !empty($data['release_date']) ? new \DateTimeImmutable($data['release_date']) : null;

        // Extraemos solo los nombres o IDs de los géneros del array que devuelve fetchMovie
        $genres = array_map(fn($g) => $g['name'], $data['genres'] ?? []);

        // 4. Creamos tu DTO Inmutable usando el constructor (Promoción de propiedades)
        $dto = new MovieUpsertRequest(
            tmdbId: (int) $data['id'],
            title: $data['title'],
            overview: $data['overview'] ?? null,
            posterPath: $data['poster_path'] ?? null,
            releaseDate: $releaseDate,
            genres: $genres
        );

        // 5. Reutilizamos tu lógica de persistencia que ya sabe manejar el DTO
        $movie = $this->findOrCreateFromUpsertDTO($dto);

        // Guardamos los cambios
        $this->em->flush();

        return $movie;
    }

    /**
     * Obtiene el catálogo de búsqueda transformado en DTOs.
     */
    public function getSearchCatalog(string $query, int $page = 1): array
    {
        // Si la consulta está vacía, podríamos devolver populares o una lista vacía.
        // De momento, delegamos directamente al servicio de TMDB.
        if (empty(trim($query))) {
            return $this->tmdbService->fetchPopularCatalog($page);
        }

        return $this->tmdbService->searchCatalog($query, $page);
    }
}
