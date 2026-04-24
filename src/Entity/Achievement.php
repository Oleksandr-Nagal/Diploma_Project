<?php

namespace App\Entity;

use App\Repository\AchievementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchievementRepository::class)]
class Achievement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'achievements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'achievements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $iconUrl = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $steamAchievementId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $unlockedAt = null;

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getGame(): ?Game { return $this->game; }
    public function setGame(?Game $game): static { $this->game = $game; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getIconUrl(): ?string { return $this->iconUrl; }
    public function setIconUrl(?string $iconUrl): static { $this->iconUrl = $iconUrl; return $this; }

    public function getSteamAchievementId(): ?string { return $this->steamAchievementId; }
    public function setSteamAchievementId(?string $steamAchievementId): static { $this->steamAchievementId = $steamAchievementId; return $this; }

    public function getUnlockedAt(): ?\DateTimeInterface { return $this->unlockedAt; }
    public function setUnlockedAt(?\DateTimeInterface $unlockedAt): static { $this->unlockedAt = $unlockedAt; return $this; }
}
