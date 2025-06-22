<?php

namespace App\Movies\Entity;

use App\Movies\Repository\MovieRepository;
use App\Users\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ORM\Table(name: "movie")]
class Movie
{
    #[Groups("movie:read")]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[Groups("movie:read")]
    #[ORM\Column(length: 255)]
    private ?string $title_movie = null;

    #[Groups("movie:read")]
    #[ORM\Column(length: 255)]
    private ?string $title_original = null;

    #[Groups("movie:read")]
    #[ORM\ManyToMany(targetEntity: Genre::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'movie_genre')]
    private Collection $genres;

    #[Groups("movie:read")]
    #[ORM\Column(type: 'text')]
    private ?string $overview = null;

    #[Groups("movie:read")]
    #[ORM\Column]
    private ?\DateTime $release_date = null;

    #[Groups("movie:read")]
    #[ORM\Column]
    private ?float $vote_average = null;

    #[Groups("movie:read")]
    #[ORM\Column(nullable: true)]
    private ?int $vote_count = null;

    #[Groups("movie:read")]
    #[ORM\Column(nullable: true)]
    private ?float $popularity = null;

    #[Groups("movie:read")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $original_languaje = null;

    #[Groups("movie:read")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster_path = null;

    #[Groups("movie:read")]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backdrop_path = null;

    #[Groups("movie:read")]
    #[ORM\Column]
    private ?bool $video = null;

    #[Groups("movie:read")]
    #[ORM\Column]
    private ?bool $adult = null;

    #[Groups("movie:read")]
    #[ORM\Column(type: 'boolean')]
    private ?bool $status = true;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "movies")]
    private Collection $users;

    #[ORM\Column(type: 'integer', unique: true)]
    private int $tmdbId;



    public function __construct()
    {
        $this->genres = new ArrayCollection();
    }

    public function getTmdbId(): int
    {
        return $this->tmdbId;
    }

    public function setTmdbId(int $tmdbId): static
    {
        $this->tmdbId = $tmdbId;
        return $this;
    }



    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }


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

    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): static
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): static
    {
        $this->genres->removeElement($genre);

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
