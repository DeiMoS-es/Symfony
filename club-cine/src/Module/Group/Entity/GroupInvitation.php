<?php

namespace App\Module\Group\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'app_group_invitation')]
class GroupInvitation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    private ?Group $invitedGroup = null;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $token;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    public function __construct(string $email, Group $group, \DateTimeImmutable $expiresAt)
    {
        $this->id = Uuid::uuid4();
        $this->email = $email;
        $this->invitedGroup = $group;
        $this->token = bin2hex(random_bytes(32));
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = $expiresAt;
        $this->status = 'pending';
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function getToken(): string
    {
        return $this->token;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function getInvitedGroup(): ?Group
    {
        return $this->invitedGroup;
    }
    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function getGroup(): ?Group
    {
        return $this->invitedGroup;
    }
    public function accept(): void
    {
        if ($this->status !== 'pending') {
            throw new \LogicException('Invitation already responded to.');
        }
        $this->status = 'accepted';
    }
    public function decline(): void
    {
        if ($this->status !== 'pending') {
            throw new \LogicException('Invitation already responded to.');
        }
        $this->status = 'declined';
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }
}
