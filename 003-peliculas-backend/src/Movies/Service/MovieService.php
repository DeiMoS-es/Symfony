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
}
?>