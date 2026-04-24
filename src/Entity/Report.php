<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reporter = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $reportedUser = null;

    #[ORM\ManyToOne(targetEntity: ChatMessage::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ChatMessage $reportedMessage = null;

    #[ORM\ManyToOne(targetEntity: Review::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Review $reportedReview = null;

    #[ORM\Column(length: 500)]
    private ?string $reason = null;

    #[ORM\Column(length: 20)]
    private string $status = 'pending'; // pending, reviewed, resolved

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $moderatorNote = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resolvedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $resolvedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getReporter(): ?User { return $this->reporter; }
    public function setReporter(?User $reporter): static { $this->reporter = $reporter; return $this; }

    public function getReportedUser(): ?User { return $this->reportedUser; }
    public function setReportedUser(?User $reportedUser): static { $this->reportedUser = $reportedUser; return $this; }

    public function getReportedMessage(): ?ChatMessage { return $this->reportedMessage; }
    public function setReportedMessage(?ChatMessage $reportedMessage): static { $this->reportedMessage = $reportedMessage; return $this; }

    public function getReportedReview(): ?Review { return $this->reportedReview; }
    public function setReportedReview(?Review $reportedReview): static { $this->reportedReview = $reportedReview; return $this; }

    public function getReason(): ?string { return $this->reason; }
    public function setReason(string $reason): static { $this->reason = $reason; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getModeratorNote(): ?string { return $this->moderatorNote; }
    public function setModeratorNote(?string $moderatorNote): static { $this->moderatorNote = $moderatorNote; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getResolvedAt(): ?\DateTimeInterface { return $this->resolvedAt; }
    public function setResolvedAt(?\DateTimeInterface $resolvedAt): static { $this->resolvedAt = $resolvedAt; return $this; }

    public function getResolvedBy(): ?User { return $this->resolvedBy; }
    public function setResolvedBy(?User $resolvedBy): static { $this->resolvedBy = $resolvedBy; return $this; }
}
