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

    public function findUpcomingEvents(int $limit = 20): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.startAt > :now')
            ->andWhere('e.status = :status')
            ->setParameter('now', new \DateTime())
            ->setParameter('status', 'planned')
            ->orderBy('e.startAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
