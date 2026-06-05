<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Lobby;
use App\Entity\LobbyMember;
use PHPUnit\Framework\TestCase;

class LobbyTest extends TestCase
{
    public function testDefaultStatusIsOpen(): void
    {
        $lobby = new Lobby();

        $this->assertSame('open', $lobby->getStatus());
    }

    public function testDefaultMaxMembersIsFive(): void
    {
        $lobby = new Lobby();

        $this->assertSame(5, $lobby->getMaxMembers());
    }

    public function testDefaultSkillLevelIsAny(): void
    {
        $lobby = new Lobby();

        $this->assertSame('any', $lobby->getSkillLevel());
    }

    public function testIsNotPrivateByDefault(): void
    {
        $lobby = new Lobby();

        $this->assertFalse($lobby->isPrivate());
    }

    public function testVoiceChatIsDisabledByDefault(): void
    {
        $lobby = new Lobby();

        $this->assertFalse($lobby->isVoiceChat());
    }

    public function testCreatedAtIsSetOnConstruction(): void
    {
        $lobby = new Lobby();

        $this->assertInstanceOf(\DateTimeInterface::class, $lobby->getCreatedAt());
    }

    public function testGetCurrentMemberCountOnlyCountsAccepted(): void
    {
        $lobby = new Lobby();

        $accepted = $this->createMember('accepted');
        $pending = $this->createMember('pending');
        $rejected = $this->createMember('rejected');

        $lobby->getMembers()->add($accepted);
        $lobby->getMembers()->add($pending);
        $lobby->getMembers()->add($rejected);

        $this->assertSame(1, $lobby->getCurrentMemberCount());
    }

    public function testIsFullReturnsTrueWhenAtCapacity(): void
    {
        $lobby = new Lobby();
        $lobby->setMaxMembers(2);

        $lobby->getMembers()->add($this->createMember('accepted'));
        $lobby->getMembers()->add($this->createMember('accepted'));

        $this->assertTrue($lobby->isFull());
    }

    public function testIsFullReturnsFalseWhenNotAtCapacity(): void
    {
        $lobby = new Lobby();
        $lobby->setMaxMembers(5);

        $lobby->getMembers()->add($this->createMember('accepted'));

        $this->assertFalse($lobby->isFull());
    }

    public function testIsFullIgnoresPendingMembers(): void
    {
        $lobby = new Lobby();
        $lobby->setMaxMembers(2);

        $lobby->getMembers()->add($this->createMember('accepted'));
        $lobby->getMembers()->add($this->createMember('pending'));
        $lobby->getMembers()->add($this->createMember('pending'));

        $this->assertFalse($lobby->isFull());
    }

    public function testSettersAndGetters(): void
    {
        $lobby = new Lobby();
        $game = new \App\Entity\Game();
        $user = new \App\Entity\User();

        $lobby->setTitle('CS2 Match');
        $lobby->setDescription('Looking for teammates');
        $lobby->setGame($game);
        $lobby->setOwner($user);
        $lobby->setMaxMembers(10);
        $lobby->setSkillLevel('advanced');
        $lobby->setMinAge(16);
        $lobby->setMaxAge(30);
        $lobby->setLanguage('uk');
        $lobby->setCity('Київ');
        $lobby->setStatus('full');
        $lobby->setIsPrivate(true);
        $lobby->setVoiceChat(true);

        $this->assertSame('CS2 Match', $lobby->getTitle());
        $this->assertSame('Looking for teammates', $lobby->getDescription());
        $this->assertSame($game, $lobby->getGame());
        $this->assertSame($user, $lobby->getOwner());
        $this->assertSame(10, $lobby->getMaxMembers());
        $this->assertSame('advanced', $lobby->getSkillLevel());
        $this->assertSame(16, $lobby->getMinAge());
        $this->assertSame(30, $lobby->getMaxAge());
        $this->assertSame('uk', $lobby->getLanguage());
        $this->assertSame('Київ', $lobby->getCity());
        $this->assertSame('full', $lobby->getStatus());
        $this->assertTrue($lobby->isPrivate());
        $this->assertTrue($lobby->isVoiceChat());
    }

    public function testScheduledAtAndClosedAt(): void
    {
        $lobby = new Lobby();
        $scheduled = new \DateTime('+1 day');
        $closed = new \DateTime();

        $lobby->setScheduledAt($scheduled);
        $lobby->setClosedAt($closed);

        $this->assertSame($scheduled, $lobby->getScheduledAt());
        $this->assertSame($closed, $lobby->getClosedAt());
    }

    public function testGetMessagesReturnsCollection(): void
    {
        $lobby = new Lobby();

        $this->assertCount(0, $lobby->getMessages());
    }

    public function testSettersReturnSelf(): void
    {
        $lobby = new Lobby();

        $this->assertSame($lobby, $lobby->setTitle('t'));
        $this->assertSame($lobby, $lobby->setDescription('d'));
        $this->assertSame($lobby, $lobby->setMaxMembers(5));
        $this->assertSame($lobby, $lobby->setSkillLevel('any'));
        $this->assertSame($lobby, $lobby->setMinAge(10));
        $this->assertSame($lobby, $lobby->setMaxAge(20));
        $this->assertSame($lobby, $lobby->setLanguage('en'));
        $this->assertSame($lobby, $lobby->setCity('Lviv'));
        $this->assertSame($lobby, $lobby->setStatus('open'));
        $this->assertSame($lobby, $lobby->setIsPrivate(false));
        $this->assertSame($lobby, $lobby->setVoiceChat(false));
        $this->assertSame($lobby, $lobby->setScheduledAt(null));
        $this->assertSame($lobby, $lobby->setClosedAt(null));
    }

    private function createMember(string $status): LobbyMember
    {
        $member = new LobbyMember();
        $member->setStatus($status);
        return $member;
    }
}
