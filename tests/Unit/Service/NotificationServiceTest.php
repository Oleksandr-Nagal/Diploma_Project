<?php

namespace App\Tests\Unit\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private NotificationService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new NotificationService($this->em);
    }

    public function testCreatePersistsNotification(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Notification::class));
        $this->em->expects($this->once())->method('flush');

        $notification = $this->service->create($user, 'system', 'Test message', '/test');

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertSame('system', $notification->getType());
        $this->assertSame('Test message', $notification->getMessage());
        $this->assertSame('/test', $notification->getLink());
    }

    public function testCreateWithoutLink(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $notification = $this->service->create($user, 'review', 'New review');

        $this->assertNull($notification->getLink());
    }

    public function testNotifyLobbyInvite(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (Notification $n) {
                return $n->getType() === 'lobby_invite'
                    && str_contains($n->getMessage(), 'Test Lobby')
                    && $n->getLink() === '/lobby/42';
            }));

        $this->service->notifyLobbyInvite($user, 'Test Lobby', 42);
    }

    public function testNotifyFriendRequest(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (Notification $n) {
                return $n->getType() === 'friend_request'
                    && str_contains($n->getMessage(), 'PlayerOne')
                    && $n->getLink() === '/friends';
            }));

        $this->service->notifyFriendRequest($user, 'PlayerOne');
    }

    public function testNotifyGameStart(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (Notification $n) {
                return $n->getType() === 'game_start'
                    && str_contains($n->getMessage(), 'CS2 Match')
                    && $n->getLink() === '/lobby/7';
            }));

        $this->service->notifyGameStart($user, 'CS2 Match', 7);
    }

    public function testNotifyNewReviewPositive(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (Notification $n) {
                return $n->getType() === 'review'
                    && str_contains($n->getMessage(), 'позитивний');
            }));

        $this->service->notifyNewReview($user, 'Gamer123', true);
    }

    public function testNotifyNewReviewNegative(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (Notification $n) {
                return str_contains($n->getMessage(), 'негативний');
            }));

        $this->service->notifyNewReview($user, 'Gamer123', false);
    }

    public function testNotifyEventReminder(): void
    {
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (Notification $n) {
                return $n->getType() === 'system'
                    && str_contains($n->getMessage(), 'Турнір')
                    && $n->getLink() === '/events/5';
            }));

        $this->service->notifyEventReminder($user, 'Турнір', 5);
    }

    public function testMarkAsRead(): void
    {
        $notification = new Notification();
        $notification->setIsRead(false);

        $this->em->expects($this->once())->method('flush');

        $this->service->markAsRead($notification);

        $this->assertTrue($notification->isRead());
    }
}
