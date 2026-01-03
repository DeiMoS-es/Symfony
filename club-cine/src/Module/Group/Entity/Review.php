<?php

namespace App\Module\Group\Entity;

use App\Module\Group\Repository\ReviewRepository;
use App\Module\Auth\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\Table(name: 'app_group_review')]
#[ORM\UniqueConstraint(name: 'unique_user_recommendation', columns: ['recommendation_id', 'user_id'])]
class Review
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Recommendation::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: "recommendation_id", referencedColumnName: "id", nullable: false, onDelete: 'CASCADE')]
    private Recommendation $recommendation;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'integer')]
    private int $scoreScript;

    #[ORM\Column(type: 'integer')]
    private int $scoreMainActor;

    #[ORM\Column(type: 'integer')]
    private int $scoreMainActress;

    #[ORM\Column(type: 'integer')]
    private int $scoreSecondaryActors;

    #[ORM\Column(type: 'integer')]
    private int $scoreDirector;

    #[ORM\Column(type: 'float')]
    private float $averageScore;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $comment;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Recommendation $recommendation,
        User $user,
        int $script,
        int $mainActor,
        int $mainActress,
        int $secondary,
        int $director,
        ?string $comment = null
    ) {
        if (!$recommendation->canAcceptVotes()) {
            throw new \LogicException("No se pueden registrar votos: el periodo de votación ha finalizado.");
        }

        $scores = [$script, $mainActor, $mainActress, $secondary, $director];
        foreach ($scores as $s) {
            if ($s < 1 || $s > 10) {
                throw new \InvalidArgumentException("Todas las puntuaciones deben estar entre 1 y 10.");
            }
        }

        if ($comment && mb_strlen($comment) > 255) {
            throw new \InvalidArgumentException("El comentario no puede exceder los 255 caracteres.");
        }

        $this->id = Uuid::uuid4();
        $this->recommendation = $recommendation;
        $this->user = $user;
        $this->scoreScript = $script;
        $this->scoreMainActor = $mainActor;
        $this->scoreMainActress = $mainActress;
        $this->scoreSecondaryActors = $secondary;
        $this->scoreDirector = $director;
        $this->comment = $comment;
        $this->createdAt = new \DateTimeImmutable();

        // Cálculo de la media automática
        $this->averageScore = array_sum($scores) / count($scores);
    }

    // --- GETTERS (Necesarios para Twig y lógica de negocio) ---

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getRecommendation(): Recommendation
    {
        return $this->recommendation;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getScoreScript(): int
    {
        return $this->scoreScript;
    }

    public function getScoreMainActor(): int
    {
        return $this->scoreMainActor;
    }

    public function getScoreMainActress(): int
    {
        return $this->scoreMainActress;
    }

    public function getScoreSecondaryActors(): int
    {
        return $this->scoreSecondaryActors;
    }

    public function getScoreDirector(): int
    {
        return $this->scoreDirector;
    }

    public function getAverageScore(): float
    {
        return $this->averageScore;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}