<?php

namespace App\Movies\Service;

use App\Movies\Dto\MovieInputDto;
use App\Movies\Repository\MovieRepository;
use App\Movies\Entity\Movie;
use App\Movies\Mapper\MovieMapperFromDTO;
use App\Movies\Mapper\MovieMapperToDTO;
use App\Movies\Dto\MovieOutputDto;

class MovieService
{
    private MovieRepository $movieRepository;
    private MovieMapperFromDTO $movieMapperFromDTO;
    private MovieMapperToDTO $movieMapperToDTO;

    public function __construct(MovieRepository $movieRepository, MovieMapperFromDTO $movieMapperFromDTO, MovieMapperToDTO $movieMapperToDTO)
    {
        $this->movieRepository = $movieRepository;
        $this->movieMapperFromDTO = $movieMapperFromDTO;
        $this->movieMapperToDTO = $movieMapperToDTO;
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
    public function createMovieFromDto(MovieInputDto $inputDto): MovieOutputDto
    {
        // 1️⃣ Convertimos DTO a entidad
        $movie = $this->movieMapperFromDTO->fromDto($inputDto);

        // 2️⃣ Guardamos la película
        $this->movieRepository->save($movie, true);

        // 3️⃣ Convertimos entidad a DTO de salida
        return $this->movieMapperToDTO->toDto($movie);
    }
}
