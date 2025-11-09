<?php

namespace App\Module\Auth\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: "App\Module\Auth\Repository\RefreshTokenRepository")]
#[ORM\Table(name: "refresh_tokens")]
class RefreshToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"uuid", unique:true)]
    private UuidInterface $uuid;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable:false, onDelete:"CASCADE")]
    private User $user;

    #[ORM\Column(length:255)]
    private string $tokenHash;

    #[ORM\Column(type:"datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type:"datetime_immutable")]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type:"datetime_immutable", nullable:true)]
    private ?\DateTimeImmutable $revokedAt = null;

    public function __construct(User $user, string $tokenHash, \DateTimeImmutable $expiresAt)
    {
        $this->uuid = Uuid::uuid4();
        $this->user = $user;
        $this->tokenHash = $tokenHash;
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = $expiresAt;
    }

    public function revoke(): void
    {
        $this->revokedAt = new \DateTimeImmutable();
    }

    public function isRevoked(): bool
    {
        return $this->revokedAt !== null || $this->expiresAt < new \DateTimeImmutable();
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
