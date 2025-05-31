<?php
namespace App\Movies\Service;

use App\Movies\Repository\MovieRepository;
use App\Movies\Entity\Movie;

class MovieService
{
    private MovieRepository $movieRepository;

    public function __construct(MovieRepository $movieRepository)
    {
        $this->movieRepository = $movieRepository;
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
    public function searchByTitle(string $term): array
    {
        return $this->movieRepository->findByTitle($term);
    }
}
?>