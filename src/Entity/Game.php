<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 50)]
    private ?string $genre = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxPlayers = null;

    #[ORM\Column(nullable: true)]
    private ?int $steamAppId = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\OneToMany(targetEntity: Lobby::class, mappedBy: 'game')]
    private Collection $lobbies;

    #[ORM\OneToMany(targetEntity: Achievement::class, mappedBy: 'game')]
    private Collection $achievements;

    public function __construct()
    {
        $this->lobbies = new ArrayCollection();
        $this->achievements = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): static { $this->slug = $slug; return $this; }

    public function getGenre(): ?string { return $this->genre; }
    public function setGenre(string $genre): static { $this->genre = $genre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $imageUrl): static { $this->imageUrl = $imageUrl; return $this; }

    public function getMaxPlayers(): ?int { return $this->maxPlayers; }
    public function setMaxPlayers(?int $maxPlayers): static { $this->maxPlayers = $maxPlayers; return $this; }

    public function getSteamAppId(): ?int { return $this->steamAppId; }
    public function setSteamAppId(?int $steamAppId): static { $this->steamAppId = $steamAppId; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getLobbies(): Collection { return $this->lobbies; }
    public function getAchievements(): Collection { return $this->achievements; }
}
