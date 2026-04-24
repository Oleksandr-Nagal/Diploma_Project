<?php

namespace App\Entity;

use App\Repository\FriendshipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FriendshipRepository::class)]
#[ORM\UniqueConstraint(columns: ['requester_id', 'receiver_id'])]
class Friendship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sentFriendRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requester = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'receivedFriendRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $receiver = null;

    #[ORM\Column(length: 20)]
    private string $status = 'pending'; // pending, accepted, rejected, blocked

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $acceptedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getRequester(): ?User { return $this->requester; }
    public function setRequester(?User $requester): static { $this->requester = $requester; return $this; }

    public function getReceiver(): ?User { return $this->receiver; }
    public function setReceiver(?User $receiver): static { $this->receiver = $receiver; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getAcceptedAt(): ?\DateTimeInterface { return $this->acceptedAt; }
    public function setAcceptedAt(?\DateTimeInterface $acceptedAt): static { $this->acceptedAt = $acceptedAt; return $this; }
}
