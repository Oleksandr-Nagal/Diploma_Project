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

    public function testSetAndGetBio(): void
    {
        $user = new User();
        $user->setBio('Hello world');
        $this->assertSame('Hello world', $user->getBio());
    }

    public function testSetAndGetAvatar(): void
    {
        $user = new User();
        $user->setAvatar('avatar:knight');
        $this->assertSame('avatar:knight', $user->getAvatar());
    }

    public function testSetAndGetCity(): void
    {
        $user = new User();
        $user->setCity('Київ');
        $this->assertSame('Київ', $user->getCity());
    }

    public function testSetAndGetLanguage(): void
    {
        $user = new User();
        $user->setLanguage('uk');
        $this->assertSame('uk', $user->getLanguage());
    }

    public function testSetAndGetAge(): void
    {
        $user = new User();
        $user->setAge(25);
        $this->assertSame(25, $user->getAge());
    }

    public function testSetAndGetSocialIds(): void
    {
        $user = new User();
        $user->setGoogleId('google123');
        $user->setDiscordId('discord456');
        $user->setSteamId('steam789');

        $this->assertSame('google123', $user->getGoogleId());
        $this->assertSame('discord456', $user->getDiscordId());
        $this->assertSame('steam789', $user->getSteamId());
    }

    public function testSetAndGetLastLoginAt(): void
    {
        $user = new User();
        $date = new \DateTime('2025-06-01');
        $user->setLastLoginAt($date);
        $this->assertSame($date, $user->getLastLoginAt());
    }

    public function testSetAndGetProfileTheme(): void
    {
        $user = new User();
        $user->setProfileTheme('ocean');
        $this->assertSame('ocean', $user->getProfileTheme());
    }

    public function testSetAndGetRating(): void
    {
        $user = new User();
        $user->setRating(85.5);
        $user->setTotalReviews(12);

        $this->assertSame(85.5, $user->getRating());
        $this->assertSame(12, $user->getTotalReviews());
    }

    public function testIsVerifiedDefaultFalse(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());
    }

    public function testSetIsVerified(): void
    {
        $user = new User();
        $user->setIsVerified(true);
        $this->assertTrue($user->isVerified());
    }

    public function testGetBanTimeLeftDaysFormat(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(new \DateTime('+3 days'));

        $timeLeft = $user->getBanTimeLeft();
        $this->assertStringContainsString('д', $timeLeft);
    }

    public function testGetBanTimeLeftMinutesFormat(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(new \DateTime('+30 minutes'));

        $timeLeft = $user->getBanTimeLeft();
        $this->assertStringContainsString('хв', $timeLeft);
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->eraseCredentials();
        $this->assertTrue(true);
    }

    public function testSetAndGetPassword(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');
        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testCollectionsAreInitialized(): void
    {
        $user = new User();

        $this->assertCount(0, $user->getLobbyMemberships());
        $this->assertCount(0, $user->getAchievements());
        $this->assertCount(0, $user->getReceivedReviews());
        $this->assertCount(0, $user->getGivenReviews());
        $this->assertCount(0, $user->getNotifications());
        $this->assertCount(0, $user->getSentFriendRequests());
        $this->assertCount(0, $user->getReceivedFriendRequests());
        $this->assertCount(0, $user->getOwnedLobbies());
        $this->assertCount(0, $user->getOrganizedEvents());
    }

    public function testGetFriendsReturnsEmptyByDefault(): void
    {
        $user = new User();
        $this->assertSame([], $user->getFriends());
    }
}
