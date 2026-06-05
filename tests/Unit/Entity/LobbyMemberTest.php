<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Lobby;
use App\Entity\LobbyMember;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class LobbyMemberTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $member = new LobbyMember();

        $this->assertNull($member->getId());
        $this->assertNull($member->getLobby());
        $this->assertNull($member->getUser());
        $this->assertSame('pending', $member->getStatus());
        $this->assertSame('member', $member->getRole());
        $this->assertInstanceOf(\DateTimeInterface::class, $member->getJoinedAt());
    }

    public function testSettersAndGetters(): void
    {
        $member = new LobbyMember();
        $lobby = new Lobby();
        $user = new User();
        $date = new \DateTime('2025-01-01');

        $member->setLobby($lobby);
        $member->setUser($user);
        $member->setStatus('accepted');
        $member->setRole('owner');
        $member->setJoinedAt($date);

        $this->assertSame($lobby, $member->getLobby());
        $this->assertSame($user, $member->getUser());
        $this->assertSame('accepted', $member->getStatus());
        $this->assertSame('owner', $member->getRole());
        $this->assertSame($date, $member->getJoinedAt());
    }

    public function testSettersReturnSelf(): void
    {
        $member = new LobbyMember();

        $this->assertSame($member, $member->setLobby(new Lobby()));
        $this->assertSame($member, $member->setUser(new User()));
        $this->assertSame($member, $member->setStatus('rejected'));
        $this->assertSame($member, $member->setRole('member'));
        $this->assertSame($member, $member->setJoinedAt(new \DateTime()));
    }
}
