<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Game;
use App\Entity\Lobby;
use App\Entity\User;
use App\Repository\LobbyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LobbyRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private LobbyRepository $repository;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(LobbyRepository::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        restore_exception_handler();
    }

    private function createGame(): Game
    {
        $game = new Game();
        $game->setName('CS2');
        $game->setGenre('FPS');
        $this->em->persist($game);
        return $game;
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername(explode('@', $email)[0]);
        $user->setPassword('hash');
        $this->em->persist($user);
        return $user;
    }

    public function testFindOpenLobbiesReturnsOnlyOpen(): void
    {
        $game = $this->createGame();
        $user = $this->createUser('test@test.com');

        $open = new Lobby();
        $open->setTitle('Open');
        $open->setGame($game);
        $open->setOwner($user);
        $open->setStatus('open');
        $this->em->persist($open);

        $closed = new Lobby();
        $closed->setTitle('Closed');
        $closed->setGame($game);
        $closed->setOwner($user);
        $closed->setStatus('closed');
        $closed->setClosedAt(new \DateTime());
        $this->em->persist($closed);

        $this->em->flush();

        $result = $this->repository->findOpenLobbies();

        $this->assertCount(1, $result);
        $this->assertSame('Open', $result[0]->getTitle());
    }

    public function testFindOpenLobbiesFiltersSearch(): void
    {
        $game = $this->createGame();
        $user = $this->createUser('u@t.com');

        $lobby1 = new Lobby();
        $lobby1->setTitle('CS2 Ranked');
        $lobby1->setGame($game);
        $lobby1->setOwner($user);
        $this->em->persist($lobby1);

        $lobby2 = new Lobby();
        $lobby2->setTitle('Dota Party');
        $lobby2->setGame($game);
        $lobby2->setOwner($user);
        $this->em->persist($lobby2);

        $this->em->flush();

        $result = $this->repository->findOpenLobbies(['search' => 'CS2']);
        $this->assertCount(1, $result);
        $this->assertSame('CS2 Ranked', $result[0]->getTitle());
    }

    public function testFindScheduledLobbies(): void
    {
        $game = $this->createGame();
        $user = $this->createUser('s@t.com');

        $scheduled = new Lobby();
        $scheduled->setTitle('Scheduled');
        $scheduled->setGame($game);
        $scheduled->setOwner($user);
        $scheduled->setScheduledAt(new \DateTime('+1 day'));
        $this->em->persist($scheduled);

        $notScheduled = new Lobby();
        $notScheduled->setTitle('Normal');
        $notScheduled->setGame($game);
        $notScheduled->setOwner($user);
        $this->em->persist($notScheduled);

        $this->em->flush();

        $result = $this->repository->findScheduledLobbies();
        $this->assertCount(1, $result);
        $this->assertSame('Scheduled', $result[0]->getTitle());
    }

    public function testRemoveStaleClosedLobbies(): void
    {
        $game = $this->createGame();
        $user = $this->createUser('r@t.com');

        $stale = new Lobby();
        $stale->setTitle('Stale');
        $stale->setGame($game);
        $stale->setOwner($user);
        $stale->setStatus('closed');
        $stale->setClosedAt(new \DateTime('-10 minutes'));
        $this->em->persist($stale);

        $recent = new Lobby();
        $recent->setTitle('Recent');
        $recent->setGame($game);
        $recent->setOwner($user);
        $recent->setStatus('closed');
        $recent->setClosedAt(new \DateTime('-1 minute'));
        $this->em->persist($recent);

        $this->em->flush();

        $this->repository->removeStaleClosedLobbies();

        $all = $this->repository->findAll();
        $this->assertCount(1, $all);
        $this->assertSame('Recent', $all[0]->getTitle());
    }
}
