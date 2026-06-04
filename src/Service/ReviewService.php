<?php

namespace App\Service;

use App\Entity\Lobby;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReviewService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReviewRepository $reviewRepository,
        private NotificationService $notificationService
    ) {}

    public function createReview(User $author, User $target, bool $isPositive, ?string $comment, ?Lobby $lobby = null): Review
    {
        return $this->upsertReview($author, $target, $isPositive, $comment, $lobby);
    }

    public function upsertReview(User $author, User $target, bool $isPositive, ?string $comment, ?Lobby $lobby = null): Review
    {
        $review = $this->reviewRepository->findOneBy(['author' => $author, 'target' => $target]);
        $isNew = false;

        if (!$review) {
            $review = new Review();
            $review->setAuthor($author);
            $review->setTarget($target);
            $isNew = true;
        }

        $review->setIsPositive($isPositive);
        $review->setComment($comment);
        $review->setLobby($lobby);
        $review->setUpdatedAt(new \DateTime());

        if ($isNew) {
            $this->em->persist($review);
        }
        
        $this->em->flush();
        $this->updateUserRating($target);
        $this->em->flush();

        if ($isNew) {
            $this->notificationService->notifyNewReview($target, $author->getUsername(), $isPositive);
        }

        return $review;
    }

    public function updateUserRating(User $user): void
    {
        $stats = $this->reviewRepository->calculateRating($user);
        $user->setRating($stats['rating']);
        $user->setTotalReviews($stats['total']);
    }
}
