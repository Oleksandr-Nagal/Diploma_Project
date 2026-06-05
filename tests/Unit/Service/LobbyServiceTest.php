<?php

namespace App\Tests\Unit\Service;

use App\Entity\Lobby;
use App\Entity\LobbyMember;
use App\Entity\User;
use App\Service\LobbyService;
use App\Service\NotificationService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LobbyServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private NotificationService&MockObject $notificationService;
    private LobbyService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->notificationService = $this->createMock(NotificationService::class);

        $this->service = new LobbyService($this->em, $this->notificationService);
    }

    public function testCreateLobbySetsOwnerAndStatus(): void
    {
        $owner = $this->createMock(User::class);
        $lobby = new Lobby();
        $lobby->setTitle('Test Lobby');

        $this->em->expects($this->exactly(2))->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->createLobby($owner, $lobby);

        $this->assertSame($owner, $result->getOwner());
        $this->assertSame('open', $result->getStatus());
    }

    public function testJoinPublicLobbyAcceptsImmediately(): void
    {
        $user = $this->createMock(User::class);
        $lobby = $this->createLobbyMock(maxMembers: 5, memberCount: 1, isPrivate: false, status: 'open');

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (LobbyMember $m) {
                return $m->getStatus() === 'accepted';
            }));

        $member = $this->service->joinLobby($user, $lobby);

        $this->assertInstanceOf(LobbyMember::class, $member);
    }

    public function testJoinPrivateLobbySetsPending(): void
    {
        $owner = $this->createMock(User::class);
        $user = $this->createMock(User::class);

        $lobby = $this->createLobbyMock(maxMembers: 5, memberCount: 1, isPrivate: true, status: 'open');
        $lobby->method('getOwner')->willReturn($owner);
        $lobby->method('getTitle')->willReturn('Private Lobby');
        $lobby->method('getId')->willReturn(1);

        $this->em->expects($this->once())->method('persist')
            ->with($this->callback(function (LobbyMember $m) {
                return $m->getStatus() === 'pending';
            }));

        $this->notificationService->expects($this->once())->method('create');

        $member = $this->service->joinLobby($user, $lobby);

        $this->assertInstanceOf(LobbyMember::class, $member);
    }

    public function testJoinFullLobbyReturnsNull(): void
    {
        $user = $this->createMock(User::class);
        $lobby = $this->createLobbyMock(maxMembers: 2, memberCount: 2, isPrivate: false, status: 'open');

        $member = $this->service->joinLobby($user, $lobby);

        $this->assertNull($member);
    }

    public function testJoinClosedLobbyReturnsNull(): void
    {
        $user = $this->createMock(User::class);
        $lobby = $this->createLobbyMock(maxMembers: 5, memberCount: 1, isPrivate: false, status: 'closed');

        $member = $this->service->joinLobby($user, $lobby);

        $this->assertNull($member);
    }

    public function testJoinLobbyAlreadyMemberReturnsNull(): void
    {
        $user = $this->createMock(User::class);

        $existingMember = $this->createMock(LobbyMember::class);
        $existingMember->method('getUser')->willReturn($user);

        $lobby = $this->createMock(Lobby::class);
        $lobby->method('isFull')->willReturn(false);
        $lobby->method('getStatus')->willReturn('open');
        $lobby->method('isPrivate')->willReturn(false);
        $lobby->method('getMembers')->willReturn(new ArrayCollection([$existingMember]));

        $member = $this->service->joinLobby($user, $lobby);

        $this->assertNull($member);
    }

    public function testAcceptMemberChangesStatus(): void
    {
        $user = $this->createMock(User::class);
        $lobby = $this->createMock(Lobby::class);
        $lobby->method('getTitle')->willReturn('Lobby');
        $lobby->method('getId')->willReturn(1);

        $member = new LobbyMember();
        $member->setStatus('pending');
        $member->setUser($user);
        $member->setLobby($lobby);

        $this->em->expects($this->once())->method('flush');
        $this->notificationService->expects($this->once())->method('create');

        $this->service->acceptMember($member);

        $this->assertSame('accepted', $member->getStatus());
    }

    public function testRejectMemberChangesStatus(): void
    {
        $member = new LobbyMember();
        $member->setStatus('pending');

        $this->em->expects($this->once())->method('flush');

        $this->service->rejectMember($member);

        $this->assertSame('rejected', $member->getStatus());
    }

    public function testLeaveLobbyRemovesMember(): void
    {
        $user = $this->createMock(User::class);

        $member = $this->createMock(LobbyMember::class);
        $member->method('getUser')->willReturn($user);

        $lobby = $this->createMock(Lobby::class);
        $lobby->method('getMembers')->willReturn(new ArrayCollection([$member]));
        $lobby->method('getOwner')->willReturn($this->createMock(User::class));
        $lobby->method('getStatus')->willReturn('open');

        $this->em->expects($this->once())->method('remove')->with($member);
        $this->em->expects($this->once())->method('flush');

        $this->service->leaveLobby($user, $lobby);
    }

    public function testLeaveLobbyByOwnerClosesLobby(): void
    {
        $owner = $this->createMock(User::class);

        $member = $this->createMock(LobbyMember::class);
        $member->method('getUser')->willReturn($owner);

        $lobby = $this->createMock(Lobby::class);
        $lobby->method('getMembers')->willReturn(new ArrayCollection([$member]));
        $lobby->method('getOwner')->willReturn($owner);
        $lobby->method('getStatus')->willReturn('open');
        $lobby->expects($this->once())->method('setStatus')->with('closed');

        $this->em->expects($this->once())->method('remove')->with($member);

        $this->service->leaveLobby($owner, $lobby);
    }

    public function testLeaveLobbyReopensFullLobby(): void
    {
        $user = $this->createMock(User::class);
        $owner = $this->createMock(User::class);

        $member = $this->createMock(LobbyMember::class);
        $member->method('getUser')->willReturn($user);

        $lobby = $this->createMock(Lobby::class);
        $lobby->method('getMembers')->willReturn(new ArrayCollection([$member]));
        $lobby->method('getOwner')->willReturn($owner);
        $lobby->method('getStatus')->willReturn('full');
        $lobby->expects($this->once())->method('setStatus')->with('open');

        $this->service->leaveLobby($user, $lobby);
    }

    private function createLobbyMock(int $maxMembers, int $memberCount, bool $isPrivate, string $status): Lobby&MockObject
    {
        $members = [];
        for ($i = 0; $i < $memberCount; $i++) {
            $m = $this->createMock(LobbyMember::class);
            $m->method('getStatus')->willReturn('accepted');
            $m->method('getUser')->willReturn($this->createMock(User::class));
            $members[] = $m;
        }

        $lobby = $this->createMock(Lobby::class);
        $lobby->method('getMaxMembers')->willReturn($maxMembers);
        $lobby->method('isFull')->willReturn($memberCount >= $maxMembers);
        $lobby->method('getStatus')->willReturn($status);
        $lobby->method('isPrivate')->willReturn($isPrivate);
        $lobby->method('getMembers')->willReturn(new ArrayCollection($members));

        return $lobby;
    }
}
