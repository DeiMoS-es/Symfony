<?php

namespace App\Module\Group\Entity;

use App\Module\Auth\Entity\User;
use App\Module\Movie\Entity\Movie;
use App\Module\Group\Repository\RecommendationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecommendationRepository::class)]
#[ORM\Table(name: 'app_group_recommendation')]
class Recommendation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Group $group;

    #[ORM\ManyToOne(targetEntity: Movie::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Movie $movie;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $recommendedBy;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $deadline;

    #[ORM\Column(length: 20)]
    private string $status = 'OPEN'; // OPEN, CLOSED

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $finalScore = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $totalVotes = 0;

    // --- Campos Estadísticos (Medias por categoría) ---

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $avgScript = 0;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $avgMainActor = 0;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $avgMainActress = 0;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $avgSecondaryActors = 0;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $avgDirector = 0;

    public function __construct(Group $group, Movie $movie, User $user, \DateTimeImmutable $deadline)
    {
        $this->group = $group;
        $this->movie = $movie;
        $this->recommendedBy = $user;
        $this->deadline = $deadline;
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Getters ---

    public function getId(): ?int { return $this->id; }
    public function getGroup(): Group { return $this->group; }
    public function getMovie(): Movie { return $this->movie; }
    public function getRecommendedBy(): User { return $this->recommendedBy; }
    public function getDeadline(): \DateTimeImmutable { return $this->deadline; }
    public function getStatus(): string { return $this->status; }
    public function getFinalScore(): ?float { return $this->finalScore; }
    public function getTotalVotes(): int { return $this->totalVotes; }

    public function getAvgScript(): float { return $this->avgScript; }
    public function getAvgMainActor(): float { return $this->avgMainActor; }
    public function getAvgMainActress(): float { return $this->avgMainActress; }
    public function getAvgSecondaryActors(): float { return $this->avgSecondaryActors; }
    public function getAvgDirector(): float { return $this->avgDirector; }

    // --- Lógica de Negocio ---

    /**
     * Cierra la recomendación y guarda todas las estadísticas calculadas
     */
    public function closeWithStats(float $finalScore, int $votes, array $stats): void
    {
        $this->finalScore = $finalScore;
        $this->totalVotes = $votes;
        
        $this->avgScript = $stats['script'] ?? 0;
        $this->avgMainActor = $stats['mainActor'] ?? 0;
        $this->avgMainActress = $stats['mainActress'] ?? 0;
        $this->avgSecondaryActors = $stats['secondary'] ?? 0;
        $this->avgDirector = $stats['director'] ?? 0;
        
        $this->status = 'CLOSED';
    }

    public function isClosed(): bool
    {
        return $this->status === 'CLOSED';
    }
}