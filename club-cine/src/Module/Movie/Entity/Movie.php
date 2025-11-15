<?php

namespace App\Module\Movie\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: "App\Module\Movie\Repository\MovieRepository")]
#[ORM\Table(name: "movie")]
class Movie
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private UuidInterface $id;

    #[ORM\Column(type: "integer", unique: true)]
    private int $tmdbId;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalTitle = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $originalLanguage = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $overview = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backdropPath = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?\DateTimeInterface $releaseDate = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $runtime = null;

    #[ORM\Column(type: "boolean")]
    private bool $adult = false;

    #[ORM\ManyToMany(targetEntity: Genre::class)]
    #[ORM\JoinTable(name: "movie_genres")]
    private Collection $genres;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $popularity = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $voteAverage = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $voteCount = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    public function __construct(int $tmdbId, string $title)
    {
        $this->id = Uuid::uuid4();
        $this->tmdbId = $tmdbId;
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
        $this->genres = new ArrayCollection();
    }

    // --- getters / setters ---

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTmdbId(): int
    {
        return $this->tmdbId;
    }
    public function setTmdbId(int $tmdbId): self
    {
        $this->tmdbId = $tmdbId;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getOriginalTitle(): ?string
    {
        return $this->originalTitle;
    }
    public function setOriginalTitle(?string $originalTitle): self
    {
        $this->originalTitle = $originalTitle;
        return $this;
    }

    public function getOriginalLanguage(): ?string
    {
        return $this->originalLanguage;
    }
    public function setOriginalLanguage(?string $originalLanguage): self
    {
        $this->originalLanguage = $originalLanguage;
        return $this;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }
    public function setOverview(?string $overview): self
    {
        $this->overview = $overview;
        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }
    public function setPosterPath(?string $posterPath): self
    {
        $this->posterPath = $posterPath;
        return $this;
    }

    public function getBackdropPath(): ?string
    {
        return $this->backdropPath;
    }
    public function setBackdropPath(?string $backdropPath): self
    {
        $this->backdropPath = $backdropPath;
        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }
    public function setReleaseDate(?\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    public function getRuntime(): ?int
    {
        return $this->runtime;
    }
    public function setRuntime(?int $runtime): self
    {
        $this->runtime = $runtime;
        return $this;
    }

    public function isAdult(): bool
    {
        return $this->adult;
    }
    public function setAdult(bool $adult): self
    {
        $this->adult = $adult;
        return $this;
    }

    public function getPopularity(): ?float
    {
        return $this->popularity;
    }
    public function setPopularity(?float $popularity): self
    {
        $this->popularity = $popularity;
        return $this;
    }

    public function getVoteAverage(): ?float
    {
        return $this->voteAverage;
    }
    public function setVoteAverage(?float $voteAverage): self
    {
        $this->voteAverage = $voteAverage;
        return $this;
    }

    public function getVoteCount(): ?int
    {
        return $this->voteCount;
    }
    public function setVoteCount(?int $voteCount): self
    {
        $this->voteCount = $voteCount;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }
        return $this;
    }
    public function removeGenre(Genre $genre): self
    {
        $this->genres->removeElement($genre);
        return $this;
    }
}
