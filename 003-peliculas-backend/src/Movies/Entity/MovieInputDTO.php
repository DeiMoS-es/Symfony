<?php
namespace App\Movies\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class MovieInputDTO
{
    #[Assert\NotBlank]
    public string $title_movie;

    #[Assert\NotBlank]
    public string $title_original;

    #[Assert\NotBlank]
    public string $overview;

    #[Assert\NotBlank]
    public string $release_date; // En formato 'Y-m-d'

    #[Assert\NotBlank]
    public float $vote_average;

    #[Assert\NotBlank]
    public array $genre_ids;

    public ?int $vote_count = null;
    public ?float $popularity = null;
    public ?string $original_languaje = null;
    public ?string $poster_path = null;
    public ?string $backdrop_path = null;
    public bool $video;
    public bool $adult;
}
?>
