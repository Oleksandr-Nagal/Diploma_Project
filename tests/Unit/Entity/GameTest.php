<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Game;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $game = new Game();

        $this->assertNull($game->getId());
        $this->assertNull($game->getName());
        $this->assertNull($game->getSlug());
        $this->assertNull($game->getGenre());
        $this->assertNull($game->getDescription());
        $this->assertNull($game->getImageUrl());
        $this->assertNull($game->getMaxPlayers());
        $this->assertNull($game->getSteamAppId());
        $this->assertTrue($game->isActive());
    }

    public function testSettersAndGetters(): void
    {
        $game = new Game();

        $game->setName('Counter-Strike 2');
        $game->setSlug('counter-strike-2');
        $game->setGenre('FPS');
        $game->setDescription('Tactical shooter');
        $game->setImageUrl('https://example.com/cs2.jpg');
        $game->setMaxPlayers(10);
        $game->setSteamAppId(730);
        $game->setIsActive(false);

        $this->assertSame('Counter-Strike 2', $game->getName());
        $this->assertSame('counter-strike-2', $game->getSlug());
        $this->assertSame('FPS', $game->getGenre());
        $this->assertSame('Tactical shooter', $game->getDescription());
        $this->assertSame('https://example.com/cs2.jpg', $game->getImageUrl());
        $this->assertSame(10, $game->getMaxPlayers());
        $this->assertSame(730, $game->getSteamAppId());
        $this->assertFalse($game->isActive());
    }

    public function testCollectionsInitialized(): void
    {
        $game = new Game();

        $this->assertInstanceOf(Collection::class, $game->getLobbies());
        $this->assertInstanceOf(Collection::class, $game->getAchievements());
        $this->assertCount(0, $game->getLobbies());
        $this->assertCount(0, $game->getAchievements());
    }

    public function testSettersReturnSelf(): void
    {
        $game = new Game();

        $this->assertSame($game, $game->setName('Test'));
        $this->assertSame($game, $game->setSlug('test'));
        $this->assertSame($game, $game->setGenre('RPG'));
        $this->assertSame($game, $game->setDescription('desc'));
        $this->assertSame($game, $game->setImageUrl('url'));
        $this->assertSame($game, $game->setMaxPlayers(5));
        $this->assertSame($game, $game->setSteamAppId(123));
        $this->assertSame($game, $game->setIsActive(true));
    }
}
