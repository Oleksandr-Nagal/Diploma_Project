<?php

namespace App\Repository;

use App\Entity\Friendship;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    public function findFriendship(User $user1, User $user2): ?Friendship
    {
        return $this->createQueryBuilder('f')
            ->where('(f.requester = :u1 AND f.receiver = :u2) OR (f.requester = :u2 AND f.receiver = :u1)')
            ->setParameter('u1', $user1)
            ->setParameter('u2', $user2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAcceptedFriends(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('(f.requester = :user OR f.receiver = :user)')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'accepted')
            ->getQuery()
            ->getResult();
    }

    public function findPendingRequests(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.receiver = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }
}
