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

    private function createMember(string $status): LobbyMember
    {
        $member = new LobbyMember();
        $member->setStatus($status);
        return $member;
    }
}
