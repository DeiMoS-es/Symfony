<?php

namespace App\Users\Entity;

use App\Movies\Entity\Movie;
use App\Users\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'user_movie')]
class UserMovie
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userMovies')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Movie::class, inversedBy: 'userMovies')]
    #[ORM\JoinColumn(nullable: false)]
    private Movie $movie;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $addedAt;

    #[Assert\Range(min: 1, max: 10, notInRangeMessage: 'La puntuación debe estar entre {{ min }} y {{ max }}.')]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rating = null;

    public function __construct(User $user, Movie $movie)
    {
        $this->user = $user;
        $this->movie = $movie;
        $this->addedAt = new \DateTimeImmutable();
    }

    public function getUser(): User
    {
        return $this->user;
    }
    public function getMovie(): Movie
    {
        return $this->movie;
    }
    public function getAddedAt(): \DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setRating(?int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }
}
