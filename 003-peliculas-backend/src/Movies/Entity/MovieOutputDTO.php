<?php
namespace App\Movies\Dto;

class MovieOutputDto
{
    public int $id;
    public string $title_movie;
    public string $title_original;
    public string $overview;
    public string $release_date;
    public float $vote_average;
    public ?int $vote_count;
    public ?float $popularity;
    public ?string $original_languaje;
    public ?string $poster_path;
    public ?string $backdrop_path;
    public bool $video;
    public bool $adult;

    /** @var string[] */
    public array $genres = [];
}

?>