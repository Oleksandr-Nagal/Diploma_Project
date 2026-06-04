<?php

namespace App\Tests\Unit\Service;

use App\Entity\Lobby;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Service\NotificationService;
use App\Service\ReviewService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReviewServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private ReviewRepository&MockObject $reviewRepo;
    private NotificationService&MockObject $notificationService;
    private ReviewService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->reviewRepo = $this->createMock(ReviewRepository::class);
        $this->notificationService = $this->createMock(NotificationService::class);

        $this->service = new ReviewService(
            $this->em,
            $this->reviewRepo,
            $this->notificationService
        );
    }

    public function testCreateReviewPersistsAndFlushes(): void
    {
        $author = $this->createMock(User::class);
        $author->method('getUsername')->willReturn('Author');

        $target = $this->createMock(User::class);

        $this->reviewRepo->method('calculateRating')->willReturn(['rating' => 80.0, 'total' => 5]);

        $this->em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Review::class));
        $this->em->expects($this->exactly(2))->method('flush');

        $review = $this->service->createReview($author, $target, true, 'Great player!');

        $this->assertInstanceOf(Review::class, $review);
        $this->assertTrue($review->isPositive());
        $this->assertSame('Great player!', $review->getComment());
    }

    public function testCreateReviewWithLobby(): void
    {
        $author = $this->createMock(User::class);
        $author->method('getUsername')->willReturn('Author');

        $target = $this->createMock(User::class);
        $lobby = $this->createMock(Lobby::class);

        $this->reviewRepo->method('calculateRating')->willReturn(['rating' => 50.0, 'total' => 2]);

        $this->em->expects($this->once())->method('persist');

        $review = $this->service->createReview($author, $target, false, 'AFK', $lobby);

        $this->assertFalse($review->isPositive());
        $this->assertSame($lobby, $review->getLobby());
    }

    public function testCreateReviewSendsNotification(): void
    {
        $author = $this->createMock(User::class);
        $author->method('getUsername')->willReturn('ReviewerName');

        $target = $this->createMock(User::class);

        $this->reviewRepo->method('calculateRating')->willReturn(['rating' => 90.0, 'total' => 10]);

        $this->notificationService->expects($this->once())
            ->method('notifyNewReview')
            ->with($target, 'ReviewerName', true);

        $this->service->createReview($author, $target, true, null);
    }

    public function testUpsertExistingReviewUpdatesWithoutPersist(): void
    {
        $author = $this->createMock(User::class);
        $author->method('getUsername')->willReturn('Author');

        $target = $this->createMock(User::class);
        $lobby = $this->createMock(Lobby::class);

        $existingReview = new Review();
        $existingReview->setAuthor($author);
        $existingReview->setTarget($target);
        $existingReview->setIsPositive(true);
        $existingReview->setComment('Old comment');

        $this->reviewRepo->method('findOneBy')
            ->with(['author' => $author, 'target' => $target])
            ->willReturn($existingReview);

        $this->reviewRepo->method('calculateRating')->willReturn(['rating' => 60.0, 'total' => 3]);

        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->exactly(2))->method('flush');

        $this->notificationService->expects($this->never())->method('notifyNewReview');

        $review = $this->service->upsertReview($author, $target, false, 'Updated comment', $lobby);

        $this->assertFalse($review->isPositive());
        $this->assertSame('Updated comment', $review->getComment());
        $this->assertSame($lobby, $review->getLobby());
    }

    public function testUpdateUserRatingSetsValues(): void
    {
        $user = new User();

        $this->reviewRepo->method('calculateRating')->willReturn(['rating' => 75.5, 'total' => 8]);

        $this->service->updateUserRating($user);

        $this->assertSame(75.5, $user->getRating());
        $this->assertSame(8, $user->getTotalReviews());
    }
}
