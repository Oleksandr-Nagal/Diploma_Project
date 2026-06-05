<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Achievement;
use App\Entity\Game;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AchievementTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $achievement = new Achievement();

        $this->assertNull($achievement->getId());
        $this->assertNull($achievement->getUser());
        $this->assertNull($achievement->getGame());
        $this->assertNull($achievement->getName());
        $this->assertNull($achievement->getDescription());
        $this->assertNull($achievement->getIconUrl());
        $this->assertNull($achievement->getSteamAchievementId());
        $this->assertNull($achievement->getUnlockedAt());
    }

    public function testSettersAndGetters(): void
    {
        $achievement = new Achievement();
        $user = new User();
        $game = new Game();
        $date = new \DateTime('2025-03-15');

        $achievement->setUser($user);
        $achievement->setGame($game);
        $achievement->setName('First Blood');
        $achievement->setDescription('Get your first kill');
        $achievement->setIconUrl('https://example.com/icon.png');
        $achievement->setSteamAchievementId('ACH_FIRST_BLOOD');
        $achievement->setUnlockedAt($date);

        $this->assertSame($user, $achievement->getUser());
        $this->assertSame($game, $achievement->getGame());
        $this->assertSame('First Blood', $achievement->getName());
        $this->assertSame('Get your first kill', $achievement->getDescription());
        $this->assertSame('https://example.com/icon.png', $achievement->getIconUrl());
        $this->assertSame('ACH_FIRST_BLOOD', $achievement->getSteamAchievementId());
        $this->assertSame($date, $achievement->getUnlockedAt());
    }

    public function testSettersReturnSelf(): void
    {
        $achievement = new Achievement();

        $this->assertSame($achievement, $achievement->setUser(new User()));
        $this->assertSame($achievement, $achievement->setGame(new Game()));
        $this->assertSame($achievement, $achievement->setName('Test'));
        $this->assertSame($achievement, $achievement->setDescription('desc'));
        $this->assertSame($achievement, $achievement->setIconUrl('url'));
        $this->assertSame($achievement, $achievement->setSteamAchievementId('id'));
        $this->assertSame($achievement, $achievement->setUnlockedAt(new \DateTime()));
    }

    public function testNullableFields(): void
    {
        $achievement = new Achievement();

        $achievement->setDescription(null);
        $achievement->setIconUrl(null);
        $achievement->setSteamAchievementId(null);
        $achievement->setUnlockedAt(null);

        $this->assertNull($achievement->getDescription());
        $this->assertNull($achievement->getIconUrl());
        $this->assertNull($achievement->getSteamAchievementId());
        $this->assertNull($achievement->getUnlockedAt());
    }
}
