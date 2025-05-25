<?php

namespace App\Movies\Entity;

use App\Movies\Repository\MovieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\Table(name: "movie")]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title_movie = null;

    #[ORM\Column(length: 255)]
    private ?string $title_original = null;

    #[ORM\Column(type: Types::JSON)]
    private array $genre_ids = [];

    #[ORM\Column(length: 255)]
    private ?string $overview = null;

    #[ORM\Column]
    private ?\DateTime $release_date = null;

    #[ORM\Column]
    private ?float $vote_average = null;

    #[ORM\Column(nullable: true)]
    private ?int $vote_count = null;

    #[ORM\Column(nullable: true)]
    private ?float $popularity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $original_languaje = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster_path = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backdrop_path = null;

    #[ORM\Column]
    private ?bool $video = null;

    #[ORM\Column]
    private ?bool $adult = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitleMovie(): ?string
    {
        return $this->title_movie;
    }

    public function setTitleMovie(string $title_movie): static
    {
        $this->title_movie = $title_movie;

        return $this;
    }

    public function getTitleOriginal(): ?string
    {
        return $this->title_original;
    }

    public function setTitleOriginal(string $title_original): static
    {
        $this->title_original = $title_original;

        return $this;
    }

    public function getGenreIds(): array
    {
        return $this->genre_ids;
    }

    public function setGenreIds(array $genre_ids): static
    {
        $this->genre_ids = $genre_ids;

        return $this;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function setOverview(string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->release_date;
    }

    public function setReleaseDate(\DateTimeInterface  $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getVoteAverage(): ?float
    {
        return $this->vote_average;
    }

    public function setVoteAverage(float $vote_average): static
    {
        $this->vote_average = $vote_average;

        return $this;
    }

    public function getVoteCount(): ?int
    {
        return $this->vote_count;
    }

    public function setVoteCount(?int $vote_count): static
    {
        $this->vote_count = $vote_count;

        return $this;
    }

    public function getPopularity(): ?float
    {
        return $this->popularity;
    }

    public function setPopularity(?float $popularity): static
    {
        $this->popularity = $popularity;

        return $this;
    }

    public function getOriginalLanguaje(): ?string
    {
        return $this->original_languaje;
    }

    public function setOriginalLanguaje(?string $original_languaje): static
    {
        $this->original_languaje = $original_languaje;

        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->poster_path;
    }

    public function setPosterPath(?string $poster_path): static
    {
        $this->poster_path = $poster_path;

        return $this;
    }

    public function getBackdropPath(): ?string
    {
        return $this->backdrop_path;
    }

    public function setBackdropPath(?string $backdrop_path): static
    {
        $this->backdrop_path = $backdrop_path;

        return $this;
    }

    public function isVideo(): ?bool
    {
        return $this->video;
    }

    public function setVideo(bool $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function isAdult(): ?bool
    {
        return $this->adult;
    }

    public function setAdult(bool $adult): static
    {
        $this->adult = $adult;

        return $this;
    }
}
