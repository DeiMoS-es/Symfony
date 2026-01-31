<?php
namespace App\Module\Group\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'group_invitations')]
class GroupInvitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 64, unique: true)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: "target_group_id", referencedColumnName: "id", nullable: false)]
    private Group $targetGroup;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    public function __construct(string $email, Group $group)
    {
        $this->email = $email;
        $this->targetGroup = $group;
        $this->token = bin2hex(random_bytes(32));
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = new \DateTimeImmutable('+48 hours');
    }

    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getToken(): string { return $this->token; }
    public function getTargetGroup(): Group { return $this->targetGroup; }
    public function getExpiresAt(): \DateTimeImmutable { return $this->expiresAt; }

    public function isExpired(): bool {
        return new \DateTimeImmutable() > $this->expiresAt;
    }
}
