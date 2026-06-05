<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Lobby;
use App\Entity\Review;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $review = new Review();

        $this->assertNull($review->getId());
        $this->assertNull($review->getAuthor());
        $this->assertNull($review->getTarget());
        $this->assertNull($review->getLobby());
        $this->assertTrue($review->isPositive());
        $this->assertNull($review->getComment());
        $this->assertInstanceOf(\DateTimeInterface::class, $review->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $review->getUpdatedAt());
    }

    public function testSettersAndGetters(): void
    {
        $review = new Review();
        $author = new User();
        $target = new User();
        $lobby = new Lobby();

        $review->setAuthor($author);
        $review->setTarget($target);
        $review->setLobby($lobby);
        $review->setIsPositive(false);
        $review->setComment('Bad player');

        $this->assertSame($author, $review->getAuthor());
        $this->assertSame($target, $review->getTarget());
        $this->assertSame($lobby, $review->getLobby());
        $this->assertFalse($review->isPositive());
        $this->assertSame('Bad player', $review->getComment());
    }

    public function testSettersReturnSelf(): void
    {
        $review = new Review();

        $this->assertSame($review, $review->setAuthor(new User()));
        $this->assertSame($review, $review->setTarget(new User()));
        $this->assertSame($review, $review->setLobby(new Lobby()));
        $this->assertSame($review, $review->setIsPositive(true));
        $this->assertSame($review, $review->setComment('test'));
        $this->assertSame($review, $review->setCreatedAt(new \DateTime()));
        $this->assertSame($review, $review->setUpdatedAt(new \DateTime()));
    }

    public function testLobbyCanBeNull(): void
    {
        $review = new Review();
        $review->setLobby(null);

        $this->assertNull($review->getLobby());
    }
}
