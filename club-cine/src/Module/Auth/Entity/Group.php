<?php

namespace App\Module\Auth\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: "App\Module\Auth\Repository\GroupRepository")]
#[ORM\Table(name: 'app_group')]
class Group {

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $group_id;

    #[ORM\Column(length: 100, unique: true)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    public function __construct(string $name, ?string $description = null)
    {
        $this->group_id = Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive = true;
    }

    // --- getters y setters ---

    public function getGroupId(): UuidInterface
    {
        return $this->group_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
    
}