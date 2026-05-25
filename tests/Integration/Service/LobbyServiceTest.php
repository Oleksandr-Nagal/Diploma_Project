<?php

namespace App\Tests\Integration\Service;

use App\Entity\Game;
use App\Entity\Lobby;
use App\Entity\LobbyMember;
use App\Entity\User;
use App\Service\LobbyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LobbyServiceTest extends KernelTestCase
{
    private LobbyService $lobbyService;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->lobbyService = $container->get(LobbyService::class);

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    private function createUser(string $email, string $username): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword('hashed_password');

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function createGame(string $name = 'CS2'): Game
    {
        $game = new Game();
        $game->setName($name);
        $game->setGenre('shooter');

        $this->em->persist($game);
        $this->em->flush();

        return $game;
    }

    public function testFullLobbyLifecycleCreateJoinLeave(): void
    {
        $owner = $this->createUser('owner@test.com', 'owner');
        $player1 = $this->createUser('p1@test.com', 'player1');
        $player2 = $this->createUser('p2@test.com', 'player2');
        $game = $this->createGame();

        $lobby = new Lobby();
        $lobby->setTitle('CS2 Ranked');
        $lobby->setGame($game);
        $lobby->setMaxMembers(3);

        $result = $this->lobbyService->createLobby($owner, $lobby);
        $lobbyId = $lobby->getId();

        $this->assertNotNull($lobbyId);
        $this->assertSame('open', $result->getStatus());
        $this->assertSame($owner, $result->getOwner());

        $this->em->refresh($lobby);
        $this->assertSame(1, $lobby->getCurrentMemberCount());
        $ownerMember = $lobby->getMembers()->first();
        $this->assertSame('owner', $ownerMember->getRole());
        $this->assertSame('accepted', $ownerMember->getStatus());

        $this->lobbyService->joinLobby($player1, $lobby);
        $this->em->refresh($lobby);
        $this->assertSame(2, $lobby->getCurrentMemberCount());
        $this->assertSame('open', $lobby->getStatus());

        $this->lobbyService->joinLobby($player2, $lobby);
        $this->em->refresh($lobby);
        $this->assertSame(3, $lobby->getCurrentMemberCount());
        $this->assertSame('full', $lobby->getStatus());

        $this->lobbyService->leaveLobby($player1, $lobby);
        $this->em->refresh($lobby);
        $this->assertSame(2, $lobby->getCurrentMemberCount());
        $this->assertSame('open', $lobby->getStatus());
    }

    public function testPrivateLobbyJoinRequestFlow(): void
    {
        $owner = $this->createUser('owner@test.com', 'owner');
        $player = $this->createUser('player@test.com', 'player');
        $game = $this->createGame();

        $lobby = new Lobby();
        $lobby->setTitle('Private Lobby');
        $lobby->setGame($game);
        $lobby->setMaxMembers(5);
        $lobby->setIsPrivate(true);

        $this->lobbyService->createLobby($owner, $lobby);
        $this->em->refresh($lobby);

        $member = $this->lobbyService->joinLobby($player, $lobby);
        $this->assertSame('pending', $member->getStatus());

        $lobbyId = $lobby->getId();
        $memberId = $member->getId();
        $this->em->clear();

        $lobby = $this->em->getRepository(Lobby::class)->find($lobbyId);
        $this->assertSame(1, $lobby->getCurrentMemberCount());

        $member = $this->em->getRepository(LobbyMember::class)->find($memberId);
        $this->lobbyService->acceptMember($member);

        $this->em->clear();
        $lobby = $this->em->getRepository(Lobby::class)->find($lobbyId);
        $this->assertSame(2, $lobby->getCurrentMemberCount());

        $member = $this->em->getRepository(LobbyMember::class)->find($memberId);
        $this->assertSame('accepted', $member->getStatus());
    }

    public function testPrivateLobbyRejectFlow(): void
    {
        $owner = $this->createUser('owner@test.com', 'owner');
        $player = $this->createUser('player@test.com', 'player');
        $game = $this->createGame();

        $lobby = new Lobby();
        $lobby->setTitle('Exclusive Lobby');
        $lobby->setGame($game);
        $lobby->setMaxMembers(5);
        $lobby->setIsPrivate(true);

        $this->lobbyService->createLobby($owner, $lobby);
        $this->em->refresh($lobby);

        $member = $this->lobbyService->joinLobby($player, $lobby);
        $memberId = $member->getId();
        $lobbyId = $lobby->getId();

        $this->lobbyService->rejectMember($member);

        $this->em->clear();
        $member = $this->em->getRepository(LobbyMember::class)->find($memberId);
        $lobby = $this->em->getRepository(Lobby::class)->find($lobbyId);

        $this->assertSame('rejected', $member->getStatus());
        $this->assertSame(1, $lobby->getCurrentMemberCount());
    }

    public function testCannotJoinFullOrClosedLobby(): void
    {
        $owner = $this->createUser('owner@test.com', 'owner');
        $player1 = $this->createUser('p1@test.com', 'player1');
        $player2 = $this->createUser('p2@test.com', 'player2');
        $latePlayer = $this->createUser('late@test.com', 'lateplayer');
        $game = $this->createGame();

        $lobby = new Lobby();
        $lobby->setTitle('Small Lobby');
        $lobby->setGame($game);
        $lobby->setMaxMembers(2);

        $this->lobbyService->createLobby($owner, $lobby);
        $this->em->refresh($lobby);
        $this->lobbyService->joinLobby($player1, $lobby);

        $this->em->refresh($lobby);
        $result = $this->lobbyService->joinLobby($latePlayer, $lobby);
        $this->assertNull($result);

        $lobbyId = $lobby->getId();
        $this->em->clear();
        $lobby = $this->em->getRepository(Lobby::class)->find($lobbyId);
        $lobby->setStatus('closed');
        $this->em->flush();

        $result = $this->lobbyService->joinLobby($player2, $lobby);
        $this->assertNull($result);
    }

    public function testOwnerLeavingClosesLobbyForAll(): void
    {
        $owner = $this->createUser('owner@test.com', 'owner');
        $player = $this->createUser('player@test.com', 'player');
        $game = $this->createGame();

        $lobby = new Lobby();
        $lobby->setTitle('Team Lobby');
        $lobby->setGame($game);
        $lobby->setMaxMembers(5);

        $this->lobbyService->createLobby($owner, $lobby);
        $this->em->refresh($lobby);
        $this->lobbyService->joinLobby($player, $lobby);

        $lobbyId = $lobby->getId();
        $this->em->clear();

        $lobby = $this->em->getRepository(Lobby::class)->find($lobbyId);
        $owner = $this->em->getRepository(User::class)->findOneBy(['email' => 'owner@test.com']);

        $this->lobbyService->leaveLobby($owner, $lobby);

        $this->em->clear();
        $lobby = $this->em->getRepository(Lobby::class)->find($lobbyId);

        $this->assertSame('closed', $lobby->getStatus());
    }

    public function testDuplicateJoinPrevented(): void
    {
        $owner = $this->createUser('owner@test.com', 'owner');
        $player = $this->createUser('player@test.com', 'player');
        $game = $this->createGame();

        $lobby = new Lobby();
        $lobby->setTitle('Test Lobby');
        $lobby->setGame($game);
        $lobby->setMaxMembers(5);

        $this->lobbyService->createLobby($owner, $lobby);
        $this->em->refresh($lobby);
        $this->lobbyService->joinLobby($player, $lobby);
        $this->em->refresh($lobby);

        $duplicate = $this->lobbyService->joinLobby($player, $lobby);
        $this->assertNull($duplicate);

        $lobbyId = $lobby->getId();
        $this->em->clear();
        $lobby = $this->em->getRepository(Lobby::class)->find($lobbyId);
        $this->assertSame(2, $lobby->getCurrentMemberCount());
    }
}
