<?php

namespace App\Module\Group\Entity;

use App\Module\Group\Repository\RecommendationRepository;
use App\Module\Auth\Entity\User;
use App\Module\Movie\Entity\Movie;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: RecommendationRepository::class)]
#[ORM\Table(name: 'app_group_recommendation')]
class Recommendation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Group $group;

    #[ORM\ManyToOne(targetEntity: Movie::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Movie $movie;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $recommender;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $deadline;

    #[ORM\Column(length: 20)]
    private string $status; // 'OPEN', 'CLOSED'

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $finalScore = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalVotes = 0;

    public function __construct(Group $group, Movie $movie, User $recommender, \DateTimeImmutable $deadline)
    {
        $this->id = Uuid::uuid4();
        $this->group = $group;
        $this->movie = $movie;
        $this->recommender = $recommender;
        $this->deadline = $deadline;
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'OPEN';
        $this->totalVotes = 0;
    }

    // --- Métodos de Lógica de Negocio ---

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->deadline;
    }

    public function canAcceptVotes(): bool
    {
        return $this->status === 'OPEN' && !$this->isExpired();
    }

    public function closeWithScore(float $score, int $votes): void
    {
        $this->finalScore = $score;
        $this->totalVotes = $votes;
        $this->status = 'CLOSED';
    }

    // --- Getters y Setters ---

    public function getId(): UuidInterface { return $this->id; }

    public function getGroup(): Group { return $this->group; }

    public function getMovie(): Movie { return $this->movie; }

    public function getRecommender(): User { return $this->recommender; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getDeadline(): \DateTimeImmutable { return $this->deadline; }

    public function getStatus(): string { return $this->status; }

    public function setStatus(string $status): void { $this->status = $status; }

    public function getFinalScore(): ?float { return $this->finalScore; }

    public function getTotalVotes(): int { return $this->totalVotes; }
}