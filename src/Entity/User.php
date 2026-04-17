<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Ця електронна адреса вже зареєстрована')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discordId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $steamId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;

    #[ORM\Column]
    private bool $isPremium = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $premiumExpiresAt = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private bool $isBanned = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $bannedUntil = null;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    private float $rating = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $totalReviews = 0;

    #[ORM\OneToMany(targetEntity: LobbyMember::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $lobbyMemberships;

    #[ORM\OneToMany(targetEntity: Achievement::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $achievements;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'target', orphanRemoval: true)]
    private Collection $receivedReviews;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'author')]
    private Collection $givenReviews;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\OneToMany(targetEntity: Friendship::class, mappedBy: 'requester')]
    private Collection $sentFriendRequests;

    #[ORM\OneToMany(targetEntity: Friendship::class, mappedBy: 'receiver')]
    private Collection $receivedFriendRequests;

    #[ORM\OneToMany(targetEntity: Lobby::class, mappedBy: 'owner')]
    private Collection $ownedLobbies;

    #[ORM\OneToMany(targetEntity: GameEvent::class, mappedBy: 'organizer')]
    private Collection $organizedEvents;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->lobbyMemberships = new ArrayCollection();
        $this->achievements = new ArrayCollection();
        $this->receivedReviews = new ArrayCollection();
        $this->givenReviews = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->sentFriendRequests = new ArrayCollection();
        $this->receivedFriendRequests = new ArrayCollection();
        $this->ownedLobbies = new ArrayCollection();
        $this->organizedEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $bio): static { $this->bio = $bio; return $this; }

    public function getAvatar(): ?string { return $this->avatar; }
    public function setAvatar(?string $avatar): static { $this->avatar = $avatar; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(?string $city): static { $this->city = $city; return $this; }

    public function getLanguage(): ?string { return $this->language; }
    public function setLanguage(?string $language): static { $this->language = $language; return $this; }

    public function getAge(): ?int { return $this->age; }
    public function setAge(?int $age): static { $this->age = $age; return $this; }

    public function getGoogleId(): ?string { return $this->googleId; }
    public function setGoogleId(?string $googleId): static { $this->googleId = $googleId; return $this; }

    public function getDiscordId(): ?string { return $this->discordId; }
    public function setDiscordId(?string $discordId): static { $this->discordId = $discordId; return $this; }

    public function getSteamId(): ?string { return $this->steamId; }
    public function setSteamId(?string $steamId): static { $this->steamId = $steamId; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getLastLoginAt(): ?\DateTimeInterface { return $this->lastLoginAt; }
    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): static { $this->lastLoginAt = $lastLoginAt; return $this; }

    public function isPremium(): bool { return $this->isPremium; }
    public function setIsPremium(bool $isPremium): static { $this->isPremium = $isPremium; return $this; }

    public function getPremiumExpiresAt(): ?\DateTimeInterface { return $this->premiumExpiresAt; }
    public function setPremiumExpiresAt(?\DateTimeInterface $premiumExpiresAt): static { $this->premiumExpiresAt = $premiumExpiresAt; return $this; }

    public function isVerified(): bool { return $this->isVerified; }
    public function setIsVerified(bool $isVerified): static { $this->isVerified = $isVerified; return $this; }

    public function isBanned(): bool
    {
        if ($this->isBanned && $this->bannedUntil !== null && $this->bannedUntil < new \DateTime()) {
            return false;
        }
        return $this->isBanned;
    }
    public function setIsBanned(bool $isBanned): static { $this->isBanned = $isBanned; return $this; }

    public function getBannedUntil(): ?\DateTimeInterface { return $this->bannedUntil; }
    public function setBannedUntil(?\DateTimeInterface $bannedUntil): static { $this->bannedUntil = $bannedUntil; return $this; }

    public function getBanTimeLeft(): ?string
    {
        if (!$this->isBanned || !$this->bannedUntil) return null;
        $now = new \DateTime();
        if ($this->bannedUntil <= $now) return null;
        $diff = $now->diff($this->bannedUntil);
        if ($diff->d > 0) return $diff->d . 'д ' . $diff->h . 'г';
        if ($diff->h > 0) return $diff->h . 'г ' . $diff->i . 'хв';
        return $diff->i . 'хв';
    }

    public function getRating(): float { return $this->rating; }
    public function setRating(float $rating): static { $this->rating = $rating; return $this; }

    public function getTotalReviews(): int { return $this->totalReviews; }
    public function setTotalReviews(int $totalReviews): static { $this->totalReviews = $totalReviews; return $this; }

    public function getLobbyMemberships(): Collection { return $this->lobbyMemberships; }
    public function getAchievements(): Collection { return $this->achievements; }
    public function getReceivedReviews(): Collection { return $this->receivedReviews; }
    public function getGivenReviews(): Collection { return $this->givenReviews; }
    public function getNotifications(): Collection { return $this->notifications; }
    public function getSentFriendRequests(): Collection { return $this->sentFriendRequests; }
    public function getReceivedFriendRequests(): Collection { return $this->receivedFriendRequests; }
    public function getOwnedLobbies(): Collection { return $this->ownedLobbies; }
    public function getOrganizedEvents(): Collection { return $this->organizedEvents; }

    public function getFriends(): array
    {
        $friends = [];
        foreach ($this->sentFriendRequests as $fr) {
            if ($fr->getStatus() === 'accepted') {
                $friends[] = $fr->getReceiver();
            }
        }
        foreach ($this->receivedFriendRequests as $fr) {
            if ($fr->getStatus() === 'accepted') {
                $friends[] = $fr->getRequester();
            }
        }
        return $friends;
    }
}
