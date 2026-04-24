<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\UniqueConstraint(columns: ['author_id', 'target_id', 'lobby_id'])]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'givenReviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'receivedReviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $target = null;

    #[ORM\ManyToOne(targetEntity: Lobby::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Lobby $lobby = null;

    #[ORM\Column]
    private bool $isPositive = true;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getAuthor(): ?User { return $this->author; }
    public function setAuthor(?User $author): static { $this->author = $author; return $this; }

    public function getTarget(): ?User { return $this->target; }
    public function setTarget(?User $target): static { $this->target = $target; return $this; }

    public function getLobby(): ?Lobby { return $this->lobby; }
    public function setLobby(?Lobby $lobby): static { $this->lobby = $lobby; return $this; }

    public function isPositive(): bool { return $this->isPositive; }
    public function setIsPositive(bool $isPositive): static { $this->isPositive = $isPositive; return $this; }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(?string $comment): static { $this->comment = $comment; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }
}
