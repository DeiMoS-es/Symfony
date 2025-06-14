<?php
// src/Movies/Mapper/MovieMapper.php
namespace App\Movies\Mapper;

use App\Movies\Dto\MovieInputDto;
use App\Movies\Entity\Movie;
use App\Movies\Repository\GenreRepository;

class MovieMapperFromDTO
{
    public function __construct( private GenreRepository $genreRepository) {}

   public function fromDto(MovieInputDto $dto): Movie
    {
        $movie = new Movie();
        $movie->setTitleMovie($dto->title_movie)
              ->setTitleOriginal($dto->title_original)
              ->setOverview($dto->overview)
              ->setReleaseDate(new \DateTime($dto->release_date))
              ->setVoteAverage($dto->vote_average)
              ->setVoteCount($dto->vote_count)
              ->setPopularity($dto->popularity)
              ->setOriginalLanguaje($dto->original_languaje)
              ->setPosterPath($dto->poster_path)
              ->setBackdropPath($dto->backdrop_path)
              ->setVideo($dto->video)
              ->setAdult($dto->adult);

        foreach ($dto->genre_ids as $id) {
            $genre = $this->genreRepository->find($id);
            if ($genre) {
                $movie->addGenre($genre);
            }
        }

        return $movie;
    }
    
}

?>