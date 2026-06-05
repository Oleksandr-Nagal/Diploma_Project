<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserRepository $repository;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(UserRepository::class);

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

    private function createUser(string $email, string $username, float $rating = 0, int $reviews = 0): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword('hash');
        $user->setRating($rating);
        $user->setTotalReviews($reviews);
        $this->em->persist($user);
        return $user;
    }

    public function testFindByGoogleId(): void
    {
        $user = $this->createUser('g@test.com', 'guser');
        $user->setGoogleId('google123');
        $this->em->flush();

        $found = $this->repository->findByGoogleId('google123');
        $this->assertNotNull($found);
        $this->assertSame('g@test.com', $found->getEmail());

        $this->assertNull($this->repository->findByGoogleId('nonexistent'));
    }

    public function testFindByDiscordId(): void
    {
        $user = $this->createUser('d@test.com', 'duser');
        $user->setDiscordId('discord456');
        $this->em->flush();

        $found = $this->repository->findByDiscordId('discord456');
        $this->assertNotNull($found);
        $this->assertSame('d@test.com', $found->getEmail());

        $this->assertNull($this->repository->findByDiscordId('nonexistent'));
    }

    public function testFindBySteamId(): void
    {
        $user = $this->createUser('s@test.com', 'suser');
        $user->setSteamId('steam789');
        $this->em->flush();

        $found = $this->repository->findBySteamId('steam789');
        $this->assertNotNull($found);

        $this->assertNull($this->repository->findBySteamId('nonexistent'));
    }

    public function testGetLeaderboardOrdersByRating(): void
    {
        $this->createUser('low@t.com', 'low', 30.0, 5);
        $this->createUser('high@t.com', 'high', 95.0, 20);
        $this->createUser('mid@t.com', 'mid', 60.0, 10);
        $this->em->flush();

        $leaders = $this->repository->getLeaderboard(10);

        $this->assertCount(3, $leaders);
        $this->assertSame('high', $leaders[0]->getUsername());
        $this->assertSame('mid', $leaders[1]->getUsername());
        $this->assertSame('low', $leaders[2]->getUsername());
    }

    public function testGetLeaderboardExcludesBanned(): void
    {
        $banned = $this->createUser('ban@t.com', 'banned', 99.0, 50);
        $banned->setIsBanned(true);
        $this->createUser('ok@t.com', 'okuser', 50.0, 5);
        $this->em->flush();

        $leaders = $this->repository->getLeaderboard();

        $this->assertCount(1, $leaders);
        $this->assertSame('okuser', $leaders[0]->getUsername());
    }

    public function testSearchUsers(): void
    {
        $this->createUser('john@test.com', 'JohnDoe');
        $this->createUser('jane@test.com', 'JaneSmith');
        $this->em->flush();

        $results = $this->repository->searchUsers('John');
        $this->assertCount(1, $results);
        $this->assertSame('JohnDoe', $results[0]->getUsername());

        $results = $this->repository->searchUsers('test.com');
        $this->assertCount(2, $results);
    }

    public function testUpgradePassword(): void
    {
        $user = $this->createUser('up@t.com', 'upuser');
        $this->em->flush();

        $this->repository->upgradePassword($user, 'new_hashed_password');

        $this->assertSame('new_hashed_password', $user->getPassword());
    }
}
