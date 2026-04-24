<?php

namespace App\Entity;

use App\Repository\LobbyMemberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LobbyMemberRepository::class)]
class LobbyMember
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Lobby::class, inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lobby $lobby = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'lobbyMemberships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 20)]
    private string $status = 'pending'; // pending, accepted, rejected

    #[ORM\Column(length: 20)]
    private string $role = 'member'; // owner, member

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $joinedAt = null;

    public function __construct()
    {
        $this->joinedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getLobby(): ?Lobby { return $this->lobby; }
    public function setLobby(?Lobby $lobby): static { $this->lobby = $lobby; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }

    public function getJoinedAt(): ?\DateTimeInterface { return $this->joinedAt; }
    public function setJoinedAt(\DateTimeInterface $joinedAt): static { $this->joinedAt = $joinedAt; return $this; }
}
