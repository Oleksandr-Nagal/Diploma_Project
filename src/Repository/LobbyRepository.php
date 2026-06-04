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

    public function removeStaleClosedLobbies(): void
    {
        $threshold = new \DateTime('-5 minutes');
        $stale = $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->andWhere('l.closedAt < :threshold')
            ->setParameter('status', 'closed')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->getResult();

        $em = $this->getEntityManager();
        foreach ($stale as $lobby) {
            $em->remove($lobby);
        }
        if ($stale) {
            $em->flush();
        }
    }

    public function findOpenLobbies(array $filters = []): array
    {
        $this->removeStaleClosedLobbies();
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.game', 'g')
            ->leftJoin('l.owner', 'o')
            ->where('l.status = :status')
            ->setParameter('status', 'open');

        if (!empty($filters['search'])) {
            $qb->andWhere('l.title LIKE :search')->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['game'])) {
            $qb->andWhere('g.id = :gameId')->setParameter('gameId', $filters['game']);
        }
        if (!empty($filters['city'])) {
            $qb->andWhere('l.city = :city')->setParameter('city', $filters['city']);
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

        return $qb->orderBy('o.isPremium', 'DESC')
            ->addOrderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
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
