<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByTarget(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.target = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function calculateRating(User $user): array
    {
        $reviews = $this->findByTarget($user);
        $total = count($reviews);

        if ($total === 0) {
            return ['rating' => 0.0, 'total' => 0, 'positive' => 0, 'negative' => 0];
        }

        $positive = 0;
        $negative = 0;
        foreach ($reviews as $review) {
            if ($review->isPositive()) {
                $positive++;
            } else {
                $negative++;
            }
        }

        $rating = round(($positive / $total) * 100, 1);

        return [
            'rating' => $rating,
            'total' => $total,
            'positive' => $positive,
            'negative' => $negative,
        ];
    }
}
