<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReviewRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ReviewRepository $repository;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(ReviewRepository::class);

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

    private function createReview(User $author, User $target, bool $isPositive): Review
    {
        $review = new Review();
        $review->setAuthor($author);
        $review->setTarget($target);
        $review->setIsPositive($isPositive);
        $this->em->persist($review);
        return $review;
    }

    public function testFindByTarget(): void
    {
        $author1 = $this->createUser('a1@t.com');
        $author2 = $this->createUser('a2@t.com');
        $target = $this->createUser('target@t.com');
        $other = $this->createUser('other@t.com');

        $this->createReview($author1, $target, true);
        $this->createReview($author2, $target, false);
        $this->createReview($author1, $other, true);
        $this->em->flush();

        $reviews = $this->repository->findByTarget($target);
        $this->assertCount(2, $reviews);
    }

    public function testFindByTargetOrderedByDate(): void
    {
        $author1 = $this->createUser('first@t.com');
        $author2 = $this->createUser('second@t.com');
        $target = $this->createUser('t@t.com');

        $r1 = $this->createReview($author1, $target, true);
        $r1->setCreatedAt(new \DateTime('-1 day'));

        $r2 = $this->createReview($author2, $target, false);
        $r2->setCreatedAt(new \DateTime());

        $this->em->flush();

        $reviews = $this->repository->findByTarget($target);
        $this->assertSame($author2->getId(), $reviews[0]->getAuthor()->getId());
    }

    public function testCalculateRatingWithNoReviews(): void
    {
        $user = $this->createUser('empty@t.com');
        $this->em->flush();

        $result = $this->repository->calculateRating($user);

        $this->assertSame(0.0, $result['rating']);
        $this->assertSame(0, $result['total']);
        $this->assertSame(0, $result['positive']);
        $this->assertSame(0, $result['negative']);
    }

    public function testCalculateRatingWithMixedReviews(): void
    {
        $target = $this->createUser('rated@t.com');
        $a1 = $this->createUser('r1@t.com');
        $a2 = $this->createUser('r2@t.com');
        $a3 = $this->createUser('r3@t.com');
        $a4 = $this->createUser('r4@t.com');

        $this->createReview($a1, $target, true);
        $this->createReview($a2, $target, true);
        $this->createReview($a3, $target, true);
        $this->createReview($a4, $target, false);
        $this->em->flush();

        $result = $this->repository->calculateRating($target);

        $this->assertSame(75.0, $result['rating']);
        $this->assertSame(4, $result['total']);
        $this->assertSame(3, $result['positive']);
        $this->assertSame(1, $result['negative']);
    }

    public function testCalculateRatingAllPositive(): void
    {
        $target = $this->createUser('perfect@t.com');
        $a1 = $this->createUser('p1@t.com');
        $a2 = $this->createUser('p2@t.com');

        $this->createReview($a1, $target, true);
        $this->createReview($a2, $target, true);
        $this->em->flush();

        $result = $this->repository->calculateRating($target);

        $this->assertSame(100.0, $result['rating']);
        $this->assertSame(2, $result['total']);
    }
}
