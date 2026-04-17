<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }
        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return $this->findOneBy(['googleId' => $googleId]);
    }

    public function findByDiscordId(string $discordId): ?User
    {
        return $this->findOneBy(['discordId' => $discordId]);
    }

    public function findBySteamId(string $steamId): ?User
    {
        return $this->findOneBy(['steamId' => $steamId]);
    }

    public function getLeaderboard(int $limit = 50): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.isBanned = false')
            ->orderBy('u.rating', 'DESC')
            ->addOrderBy('u.totalReviews', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchUsers(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.username LIKE :q')
            ->orWhere('u.email LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}
