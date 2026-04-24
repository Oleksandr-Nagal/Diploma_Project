<?php

namespace App\Repository;

use App\Entity\Lobby;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LobbyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lobby::class);
    }

    public function findOpenLobbies(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.game', 'g')
            ->where('l.status = :status')
            ->setParameter('status', 'open');

        if (!empty($filters['search'])) {
            $qb->andWhere('l.title LIKE :search')->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['game'])) {
            $qb->andWhere('g.id = :gameId')->setParameter('gameId', $filters['game']);
        }
        if (!empty($filters['city'])) {
            $qb->andWhere('l.city LIKE :city')->setParameter('city', '%' . $filters['city'] . '%');
        }
        if (!empty($filters['language'])) {
            $qb->andWhere('l.language = :lang')->setParameter('lang', $filters['language']);
        }
        if (!empty($filters['skillLevel'])) {
            $qb->andWhere('l.skillLevel = :skill')->setParameter('skill', $filters['skillLevel']);
        }
        if (!empty($filters['voiceChat'])) {
            $qb->andWhere('l.voiceChat = true');
        }
        if (!empty($filters['genre'])) {
            $qb->andWhere('g.genre = :genre')->setParameter('genre', $filters['genre']);
        }

        return $qb->orderBy('l.createdAt', 'DESC')->getQuery()->getResult();
    }

    public function findScheduledLobbies(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.scheduledAt IS NOT NULL')
            ->andWhere('l.scheduledAt > :now')
            ->andWhere('l.status = :status')
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'open')
            ->orderBy('l.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
