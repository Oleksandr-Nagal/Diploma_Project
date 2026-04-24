<?php

namespace App\Entity;

use App\Repository\LobbyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LobbyRepository::class)]
class Lobby
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'lobbies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ownedLobbies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private int $maxMembers = 5;

    #[ORM\Column(length: 20)]
    private string $skillLevel = 'any'; // beginner, intermediate, advanced, pro, any

    #[ORM\Column(nullable: true)]
    private ?int $minAge = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxAge = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 20)]
    private string $status = 'open'; // open, full, in_game, closed

    #[ORM\Column]
    private bool $isPrivate = false;

    #[ORM\Column]
    private bool $voiceChat = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $scheduledAt = null;

    #[ORM\OneToMany(targetEntity: LobbyMember::class, mappedBy: 'lobby', orphanRemoval: true)]
    private Collection $members;

    #[ORM\OneToMany(targetEntity: ChatMessage::class, mappedBy: 'lobby', orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->members = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getGame(): ?Game { return $this->game; }
    public function setGame(?Game $game): static { $this->game = $game; return $this; }

    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(?User $owner): static { $this->owner = $owner; return $this; }

    public function getMaxMembers(): int { return $this->maxMembers; }
    public function setMaxMembers(int $maxMembers): static { $this->maxMembers = $maxMembers; return $this; }

    public function getSkillLevel(): string { return $this->skillLevel; }
    public function setSkillLevel(string $skillLevel): static { $this->skillLevel = $skillLevel; return $this; }

    public function getMinAge(): ?int { return $this->minAge; }
    public function setMinAge(?int $minAge): static { $this->minAge = $minAge; return $this; }

    public function getMaxAge(): ?int { return $this->maxAge; }
    public function setMaxAge(?int $maxAge): static { $this->maxAge = $maxAge; return $this; }

    public function getLanguage(): ?string { return $this->language; }
    public function setLanguage(?string $language): static { $this->language = $language; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): static { $this->city = $city; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function isPrivate(): bool { return $this->isPrivate; }
    public function setIsPrivate(bool $isPrivate): static { $this->isPrivate = $isPrivate; return $this; }

    public function isVoiceChat(): bool { return $this->voiceChat; }
    public function setVoiceChat(bool $voiceChat): static { $this->voiceChat = $voiceChat; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getScheduledAt(): ?\DateTimeInterface { return $this->scheduledAt; }
    public function setScheduledAt(?\DateTimeInterface $scheduledAt): static { $this->scheduledAt = $scheduledAt; return $this; }

    public function getMembers(): Collection { return $this->members; }
    public function getMessages(): Collection { return $this->messages; }

    public function getCurrentMemberCount(): int
    {
        return $this->members->filter(fn(LobbyMember $m) => $m->getStatus() === 'accepted')->count();
    }

    public function isFull(): bool
    {
        return $this->getCurrentMemberCount() >= $this->maxMembers;
    }
}
