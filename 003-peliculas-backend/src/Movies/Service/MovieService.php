<?php

namespace App\Movies\Service;


use App\Movies\Entity\MovieInputDTO;
use App\Movies\Entity\MovieOutputDTO;

use App\Movies\Repository\MovieRepository;
use App\Movies\Entity\Movie;
use App\Movies\External\TmbdClient;
use App\Movies\Mapper\MovieMapperFromDTO;
use App\Movies\Mapper\MovieMapperToDTO;
use App\Movies\Repository\GenreRepository;


class MovieService
{
    private MovieRepository $movieRepository;
    private MovieMapperFromDTO $movieMapperFromDTO;
    private MovieMapperToDTO $movieMapperToDTO;
    private TmbdClient $tmdbClient;
    private GenreRepository $genreRepository;

    public function __construct(MovieRepository $movieRepository, MovieMapperFromDTO $movieMapperFromDTO, MovieMapperToDTO $movieMapperToDTO, TmbdClient $tmdbClient, GenreRepository $genreRepository)
    {
        $this->movieRepository = $movieRepository;
        $this->movieMapperFromDTO = $movieMapperFromDTO;
        $this->movieMapperToDTO = $movieMapperToDTO;
        $this->tmdbClient = $tmdbClient;
        $this->genreRepository = $genreRepository;
    }

    public function getMovieById(int $id): ?Movie
    {
        return $this->movieRepository->find($id);
    }

    public function getAllMovies(): array
    {
        return $this->movieRepository->findAll();
    }

    /**
     * Devuelve todas las películas activas.
     */
    public function getAllActiveMovies(): array
    {
        return $this->movieRepository->findBy(['status' => true]);
    }

    /**
     * Devuelve las más populares.
     */
    public function getTopPopularMovies(int $limit = 10): array
    {
        return $this->movieRepository->findMostPopular($limit);
    }

    /**
     * Buscar películas por título.
     */
    public function searchMovieByTitle(string $term): array
    {
        return $this->movieRepository->findByTitle($term);
    }
    /**
     * Guardar una película.
     */
    public function createMovieFromDto(MovieInputDTO $inputDto): MovieOutputDTO
    {
        // 1️⃣ Convertimos DTO a entidad
        $movie = $this->movieMapperFromDTO->fromDto($inputDto);

        // 2️⃣ Guardamos la película
        $this->movieRepository->save($movie, true);

        // 3️⃣ Convertimos entidad a DTO de salida
        return $this->movieMapperToDTO->toDto($movie);
    }

    /**
     * Eliminar una película por ID.
     */
    public function deleteMovieById(int $id): bool
    {
        $movie = $this->getMovieById($id);
        if (!$movie) {
            return false;
        }
        $this->movieRepository->remove($movie, true);
        return true;
    }

    public function importMovies(): int
    {
        $data = $this->tmdbClient->fetchAllMovies();
        $count = 0;

        foreach ($data as $index => $movieArray) {
            if (empty($movieArray['title']) || empty($movieArray['release_date'])) {
                continue;
            }


            $movie = new Movie();
            $movie->setTitleMovie($movieArray['title']);
            $movie->setTitleOriginal($movieArray['original_title']);
            $movie->setOverview($movieArray['overview'] ?? '');
            $movie->setReleaseDate(new \DateTime($movieArray['release_date']));
            $movie->setVoteAverage((float) $movieArray['vote_average']);
            $movie->setVoteCount($movieArray['vote_count'] ?? null);
            $movie->setPopularity($movieArray['popularity'] ?? null);
            $movie->setOriginalLanguaje($movieArray['original_language'] ?? null);
            $movie->setPosterPath($movieArray['poster_path'] ?? null);
            $movie->setBackdropPath($movieArray['backdrop_path'] ?? null);
            $movie->setVideo((bool) $movieArray['video']);
            $movie->setAdult((bool) $movieArray['adult']);
            $movie->setStatus(true);
            echo "."; // para ver que avanza

            // 🎬 Asociar géneros
            foreach ($movieArray['genre_ids'] as $genreId) {
                $genre = $this->genreRepository->findOneBy(['tmdbId' => $genreId]);
                if ($genre) {
                    $movie->addGenre($genre);
                }
            }

            $this->movieRepository->save($movie); // solo persist
            $count++;
            if ($index % 50 === 0) {
                $this->movieRepository->flush(); // guarda cada 50
            }
        }

        $this->movieRepository->flush();
        return $count;
    }
}
