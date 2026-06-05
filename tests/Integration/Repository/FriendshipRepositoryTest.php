<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\FriendshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FriendshipRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private FriendshipRepository $repository;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(FriendshipRepository::class);

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

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername(explode('@', $email)[0]);
        $user->setPassword('hash');
        $this->em->persist($user);
        return $user;
    }

    public function testFindFriendshipBothDirections(): void
    {
        $user1 = $this->createUser('a@t.com');
        $user2 = $this->createUser('b@t.com');

        $friendship = new Friendship();
        $friendship->setRequester($user1);
        $friendship->setReceiver($user2);
        $friendship->setStatus('accepted');
        $this->em->persist($friendship);
        $this->em->flush();

        $found1 = $this->repository->findFriendship($user1, $user2);
        $this->assertNotNull($found1);

        $found2 = $this->repository->findFriendship($user2, $user1);
        $this->assertNotNull($found2);

        $this->assertSame($found1->getId(), $found2->getId());
    }

    public function testFindFriendshipReturnsNullWhenNone(): void
    {
        $user1 = $this->createUser('x@t.com');
        $user2 = $this->createUser('y@t.com');
        $this->em->flush();

        $this->assertNull($this->repository->findFriendship($user1, $user2));
    }

    public function testFindAcceptedFriends(): void
    {
        $user = $this->createUser('me@t.com');
        $friend1 = $this->createUser('f1@t.com');
        $friend2 = $this->createUser('f2@t.com');
        $pending = $this->createUser('p@t.com');

        $fr1 = new Friendship();
        $fr1->setRequester($user);
        $fr1->setReceiver($friend1);
        $fr1->setStatus('accepted');
        $this->em->persist($fr1);

        $fr2 = new Friendship();
        $fr2->setRequester($friend2);
        $fr2->setReceiver($user);
        $fr2->setStatus('accepted');
        $this->em->persist($fr2);

        $fr3 = new Friendship();
        $fr3->setRequester($pending);
        $fr3->setReceiver($user);
        $fr3->setStatus('pending');
        $this->em->persist($fr3);

        $this->em->flush();

        $friends = $this->repository->findAcceptedFriends($user);
        $this->assertCount(2, $friends);
    }

    public function testFindPendingRequests(): void
    {
        $user = $this->createUser('target@t.com');
        $requester1 = $this->createUser('r1@t.com');
        $requester2 = $this->createUser('r2@t.com');

        $fr1 = new Friendship();
        $fr1->setRequester($requester1);
        $fr1->setReceiver($user);
        $fr1->setStatus('pending');
        $this->em->persist($fr1);

        $fr2 = new Friendship();
        $fr2->setRequester($requester2);
        $fr2->setReceiver($user);
        $fr2->setStatus('pending');
        $this->em->persist($fr2);

        $fr3 = new Friendship();
        $fr3->setRequester($user);
        $fr3->setReceiver($requester1);
        $fr3->setStatus('pending');
        $this->em->persist($fr3);

        $this->em->flush();

        $pending = $this->repository->findPendingRequests($user);
        $this->assertCount(2, $pending);
    }
}
