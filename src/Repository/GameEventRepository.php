<?php

namespace App\Repository;

use App\Entity\GameEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GameEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameEvent::class);
    }

    public function updateEventStatuses(): void
    {
        $now = new \DateTime();

        $this->createQueryBuilder('e')
            ->update()
            ->set('e.status', ':active')
            ->where('e.status = :planned')
            ->andWhere('e.startAt <= :now')
            ->andWhere('e.endAt IS NULL OR e.endAt > :now')
            ->setParameter('active', 'active')
            ->setParameter('planned', 'planned')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();

        $this->createQueryBuilder('e')
            ->update()
            ->set('e.status', ':completed')
            ->where('e.status = :planned OR e.status = :active')
            ->andWhere('e.endAt IS NOT NULL AND e.endAt <= :now')
            ->setParameter('completed', 'completed')
            ->setParameter('planned', 'planned')
            ->setParameter('active', 'active')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();
    }

    public function findUpcomingEvents(int $limit = 20): array
    {
        $this->updateEventStatuses();
        $this->getEntityManager()->clear();

        return $this->createQueryBuilder('e')
            ->where('e.status = :planned OR e.status = :active')
            ->setParameter('planned', 'planned')
            ->setParameter('active', 'active')
            ->orderBy('e.startAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
