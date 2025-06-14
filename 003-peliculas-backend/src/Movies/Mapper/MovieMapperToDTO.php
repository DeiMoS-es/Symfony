<?php
// src/Movies/Mapper/MovieMapper.php
namespace App\Movies\Mapper;


use App\Movies\Dto\MovieOutputDto;
use App\Movies\Entity\Genre;
use App\Movies\Entity\Movie;
use App\Movies\Repository\GenreRepository;

class MovieMapperToDTO
{
    public function __construct(private GenreRepository $genreRepository) {}

    public function toDto(Movie $movie): MovieOutputDto
    {
        $dto = new MovieOutputDto();
        $dto->id = $movie->getId();
        $dto->title_movie = $movie->getTitleMovie();
        $dto->title_original = $movie->getTitleOriginal();
        $dto->overview = $movie->getOverview();
        $dto->release_date = $movie->getReleaseDate()->format('d/m/Y');
        $dto->vote_average = $movie->getVoteAverage();
        $dto->vote_count = $movie->getVoteCount();
        $dto->popularity = $movie->getPopularity();
        $dto->original_languaje = $movie->getOriginalLanguaje();
        $dto->poster_path = $movie->getPosterPath();
        $dto->backdrop_path = $movie->getBackdropPath();
        $dto->video = $movie->isVideo();
        $dto->adult = $movie->isAdult();
        $dto->genres = array_map(fn(Genre $g) => $g->getName(), $movie->getGenres()->toArray());

        return $dto;
    }
}
