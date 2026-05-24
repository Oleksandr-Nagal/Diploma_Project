<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PremiumService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function activate(User $user, int $days = 30): void
    {
        $expiresAt = new \DateTime("+{$days} days");

        if ($user->isPremium() && $user->getPremiumExpiresAt() > new \DateTime()) {
            $expiresAt = (clone $user->getPremiumExpiresAt())->modify("+{$days} days");
        }

        $user->setIsPremium(true);
        $user->setPremiumExpiresAt($expiresAt);
        $this->entityManager->flush();
    }

    public function deactivate(User $user): void
    {
        $user->setIsPremium(false);
        $user->setPremiumExpiresAt(null);
        $this->entityManager->flush();
    }

    public function isExpired(User $user): bool
    {
        if (!$user->isPremium()) {
            return true;
        }

        if ($user->getPremiumExpiresAt() === null) {
            return false;
        }

        return $user->getPremiumExpiresAt() < new \DateTime();
    }

    public function getDaysRemaining(User $user): int
    {
        if (!$user->isPremium() || $user->getPremiumExpiresAt() === null) {
            return 0;
        }

        $now = new \DateTime();
        if ($user->getPremiumExpiresAt() <= $now) {
            return 0;
        }

        return (int) $now->diff($user->getPremiumExpiresAt())->days;
    }

    public function getUserById(int $id): ?User
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }

    public function expireOutdatedPremiums(): int
    {
        $users = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.isPremium = true')
            ->andWhere('u.premiumExpiresAt IS NOT NULL')
            ->andWhere('u.premiumExpiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($users as $user) {
            $user->setIsPremium(false);
            $user->setPremiumExpiresAt(null);
            $count++;
        }

        $this->entityManager->flush();

        return $count;
    }
}
