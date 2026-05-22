<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testIsPremiumReturnsFalseByDefault(): void
    {
        $user = new User();

        $this->assertFalse($user->isPremium());
    }

    public function testIsPremiumReturnsTrueWhenActive(): void
    {
        $user = new User();
        $user->setIsPremium(true);
        $user->setPremiumExpiresAt(new \DateTime('+30 days'));

        $this->assertTrue($user->isPremium());
    }

    public function testIsPremiumReturnsTrueWhenNoExpiry(): void
    {
        $user = new User();
        $user->setIsPremium(true);
        $user->setPremiumExpiresAt(null);

        $this->assertTrue($user->isPremium());
    }

    public function testIsBannedReturnsFalseByDefault(): void
    {
        $user = new User();

        $this->assertFalse($user->isBanned());
    }

    public function testIsBannedReturnsTrueForPermanentBan(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(null);

        $this->assertTrue($user->isBanned());
    }

    public function testIsBannedReturnsTrueForActiveTempBan(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(new \DateTime('+24 hours'));

        $this->assertTrue($user->isBanned());
    }

    public function testIsBannedReturnsFalseForExpiredTempBan(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(new \DateTime('-1 hour'));

        $this->assertFalse($user->isBanned());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetRolesIncludesAssignedRole(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetRolesNoDuplicates(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_USER']);

        $roles = $user->getRoles();

        $this->assertCount(1, $roles);
    }

    public function testGetBanTimeLeftReturnsNullWhenNotBanned(): void
    {
        $user = new User();
        $user->setIsBanned(false);

        $this->assertNull($user->getBanTimeLeft());
    }

    public function testGetBanTimeLeftReturnsNullWhenPermanent(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(null);

        $this->assertNull($user->getBanTimeLeft());
    }

    public function testGetBanTimeLeftReturnsStringWhenActive(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(new \DateTime('+2 hours'));

        $timeLeft = $user->getBanTimeLeft();

        $this->assertNotNull($timeLeft);
        $this->assertStringContainsString('г', $timeLeft);
    }

    public function testGetBanTimeLeftReturnsNullWhenExpired(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(new \DateTime('-1 hour'));

        $this->assertNull($user->getBanTimeLeft());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testCreatedAtIsSetOnConstruction(): void
    {
        $user = new User();

        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
    }

    public function testDefaultRatingIsZero(): void
    {
        $user = new User();

        $this->assertSame(0.0, $user->getRating());
        $this->assertSame(0, $user->getTotalReviews());
    }
}
