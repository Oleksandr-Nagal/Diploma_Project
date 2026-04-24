<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findActiveGames(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.isActive = true')
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByGenre(string $genre): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.genre = :genre')
            ->andWhere('g.isActive = true')
            ->setParameter('genre', $genre)
            ->getQuery()
            ->getResult();
    }
}
