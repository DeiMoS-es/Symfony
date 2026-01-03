<?php

namespace App\Module\Group\Entity;

use App\Module\Auth\Entity\User;
use App\Module\Movie\Entity\Movie;
use App\Module\Group\Repository\RecommendationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: RecommendationRepository::class)]
#[ORM\Table(name: 'app_group_recommendation')]
class Recommendation
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Group $group;

    #[ORM\ManyToOne(targetEntity: Movie::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Movie $movie;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $recommendedBy;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status; // 'OPEN', 'CLOSED'

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $deadline;

    // --- Campos de resultados tras el cierre ---
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $averageScore = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $totalVotes = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $stats = null;

    // --- Relación con las Reviews (Bidireccional) ---
    #[ORM\OneToMany(mappedBy: 'recommendation', targetEntity: Review::class)]
    private Collection $reviews;

    public function __construct(
        Group $group,
        Movie $movie,
        User $user,
        \DateTimeImmutable $deadline
    ) {
        $this->id = Uuid::uuid4();
        $this->group = $group;
        $this->movie = $movie;
        $this->recommendedBy = $user;
        $this->deadline = $deadline;
        $this->status = 'OPEN';
        $this->createdAt = new \DateTimeImmutable();
        
        // Inicializamos la colección aquí para que no sea un argumento del constructor
        $this->reviews = new ArrayCollection();
    }

    // --- Getters ---
    public function getId(): UuidInterface { return $this->id; }
    public function getGroup(): Group { return $this->group; }
    public function getMovie(): Movie { return $this->movie; }
    public function getRecommendedBy(): User { return $this->recommendedBy; }
    public function getStatus(): string { return $this->status; }
    public function getDeadline(): \DateTimeImmutable { return $this->deadline; }
    public function getAverageScore(): ?float { return $this->averageScore; }
    public function getTotalVotes(): ?int { return $this->totalVotes; }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    // --- Lógica de Negocio ---

    public function canAcceptVotes(): bool
    {
        return $this->status === 'OPEN' && new \DateTimeImmutable() < $this->deadline;
    }

    public function closeWithStats(float $average, int $total, array $stats): void
    {
        $this->status = 'CLOSED';
        $this->averageScore = $average;
        $this->totalVotes = $total;
        $this->stats = $stats;
    }
}