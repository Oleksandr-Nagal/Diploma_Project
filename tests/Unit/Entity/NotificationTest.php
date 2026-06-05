<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Notification;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $notification = new Notification();

        $this->assertNull($notification->getId());
        $this->assertNull($notification->getUser());
        $this->assertNull($notification->getType());
        $this->assertNull($notification->getMessage());
        $this->assertNull($notification->getLink());
        $this->assertFalse($notification->isRead());
        $this->assertInstanceOf(\DateTimeInterface::class, $notification->getCreatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $notification = new Notification();
        $user = new User();
        $date = new \DateTime('2025-01-01');

        $notification->setUser($user);
        $notification->setType('lobby_invite');
        $notification->setMessage('You have been invited');
        $notification->setLink('/lobby/5');
        $notification->setIsRead(true);
        $notification->setCreatedAt($date);

        $this->assertSame($user, $notification->getUser());
        $this->assertSame('lobby_invite', $notification->getType());
        $this->assertSame('You have been invited', $notification->getMessage());
        $this->assertSame('/lobby/5', $notification->getLink());
        $this->assertTrue($notification->isRead());
        $this->assertSame($date, $notification->getCreatedAt());
    }

    public function testSettersReturnSelf(): void
    {
        $notification = new Notification();

        $this->assertSame($notification, $notification->setUser(new User()));
        $this->assertSame($notification, $notification->setType('test'));
        $this->assertSame($notification, $notification->setMessage('msg'));
        $this->assertSame($notification, $notification->setLink('/test'));
        $this->assertSame($notification, $notification->setIsRead(true));
        $this->assertSame($notification, $notification->setCreatedAt(new \DateTime()));
    }
}
