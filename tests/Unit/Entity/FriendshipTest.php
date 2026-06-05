<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Friendship;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class FriendshipTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $friendship = new Friendship();

        $this->assertNull($friendship->getId());
        $this->assertNull($friendship->getRequester());
        $this->assertNull($friendship->getReceiver());
        $this->assertSame('pending', $friendship->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $friendship->getCreatedAt());
        $this->assertNull($friendship->getAcceptedAt());
    }

    public function testSettersAndGetters(): void
    {
        $friendship = new Friendship();
        $requester = new User();
        $receiver = new User();
        $created = new \DateTime('2025-01-01');
        $accepted = new \DateTime('2025-01-02');

        $friendship->setRequester($requester);
        $friendship->setReceiver($receiver);
        $friendship->setStatus('accepted');
        $friendship->setCreatedAt($created);
        $friendship->setAcceptedAt($accepted);

        $this->assertSame($requester, $friendship->getRequester());
        $this->assertSame($receiver, $friendship->getReceiver());
        $this->assertSame('accepted', $friendship->getStatus());
        $this->assertSame($created, $friendship->getCreatedAt());
        $this->assertSame($accepted, $friendship->getAcceptedAt());
    }

    public function testSettersReturnSelf(): void
    {
        $friendship = new Friendship();

        $this->assertSame($friendship, $friendship->setRequester(new User()));
        $this->assertSame($friendship, $friendship->setReceiver(new User()));
        $this->assertSame($friendship, $friendship->setStatus('rejected'));
        $this->assertSame($friendship, $friendship->setCreatedAt(new \DateTime()));
        $this->assertSame($friendship, $friendship->setAcceptedAt(new \DateTime()));
    }
}
