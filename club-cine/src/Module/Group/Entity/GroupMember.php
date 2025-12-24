<?php
namespace App\Module\Group\Entity;

use App\Module\Auth\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use App\Module\Group\Repository\GroupMemberRepository;

#[ORM\Entity(repositoryClass: GroupMemberRepository::class)]
#[ORM\Table(name: 'app_group_member')]
class GroupMember{
    
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Group $group;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 20)]
    private string $role; // 'OWNER', 'MEMBER'

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $joinedAt;

    public function __construct(Group $group, User $user, string $role = 'MEMBER')
    {
        $this->id = Uuid::uuid4();
        $this->group = $group;
        $this->user = $user;
        $this->role = $role;
        $this->joinedAt = new \DateTimeImmutable();
    }

    // --- Getters ---

    public function getId(): UuidInterface { return $this->id; }
    public function getGroup(): Group { return $this->group; }
    public function getUser(): User { return $this->user; }
    public function getRole(): string { return $this->role; }
    public function getJoinedAt(): \DateTimeImmutable { return $this->joinedAt; }
}

?>