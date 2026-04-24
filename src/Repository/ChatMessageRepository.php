<?php

namespace App\Repository;

use App\Entity\ChatMessage;
use App\Entity\Lobby;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    public function findLobbyMessages(Lobby $lobby, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.lobby = :lobby')
            ->andWhere('m.isPrivate = false')
            ->setParameter('lobby', $lobby)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPrivateMessages(User $user1, User $user2, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.isPrivate = true')
            ->andWhere(
                '(m.sender = :u1 AND m.recipient = :u2) OR (m.sender = :u2 AND m.recipient = :u1)'
            )
            ->setParameter('u1', $user1)
            ->setParameter('u2', $user2)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getConversationList(User $user): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.isPrivate = true')
            ->andWhere('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
