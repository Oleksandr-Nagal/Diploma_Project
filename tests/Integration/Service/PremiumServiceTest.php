<?php

namespace App\Tests\Integration\Service;

use App\Entity\User;
use App\Service\PremiumService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PremiumServiceTest extends KernelTestCase
{
    private PremiumService $premiumService;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->premiumService = $container->get(PremiumService::class);

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

    private function createUser(string $email = 'test@example.com', string $username = 'testuser'): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword('hashed_password');

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function testFullPremiumLifecycle(): void
    {
        $user = $this->createUser();

        $this->assertFalse($user->isPremium());
        $this->assertTrue($this->premiumService->isExpired($user));
        $this->assertSame(0, $this->premiumService->getDaysRemaining($user));

        $this->premiumService->activate($user, 30);

        $this->assertTrue($user->isPremium());
        $this->assertFalse($this->premiumService->isExpired($user));
        $this->assertGreaterThanOrEqual(29, $this->premiumService->getDaysRemaining($user));

        $userId = $user->getId();
        $this->em->clear();
        $userFromDb = $this->premiumService->getUserById($userId);

        $this->assertTrue($userFromDb->isPremium());
        $this->assertNotNull($userFromDb->getPremiumExpiresAt());

        $this->premiumService->deactivate($userFromDb);

        $this->em->clear();
        $userFromDb = $this->premiumService->getUserById($userId);

        $this->assertFalse($userFromDb->isPremium());
        $this->assertNull($userFromDb->getPremiumExpiresAt());
    }

    public function testPremiumDaysAccumulateAcrossMultiplePurchases(): void
    {
        $user = $this->createUser();

        $this->premiumService->activate($user, 30);
        $firstExpiry = clone $user->getPremiumExpiresAt();

        $this->premiumService->activate($user, 90);
        $secondExpiry = clone $user->getPremiumExpiresAt();

        $this->premiumService->activate($user, 365);

        $userId = $user->getId();
        $this->em->clear();
        $userFromDb = $this->premiumService->getUserById($userId);

        $expectedFirst = new \DateTime('+30 days');
        $diffFirst = abs($expectedFirst->getTimestamp() - $firstExpiry->getTimestamp());
        $this->assertLessThan(5, $diffFirst);

        $expectedSecond = (clone $firstExpiry)->modify('+90 days');
        $diffSecond = abs($expectedSecond->getTimestamp() - $secondExpiry->getTimestamp());
        $this->assertLessThan(5, $diffSecond);

        $expectedFinal = (clone $secondExpiry)->modify('+365 days');
        $diffFinal = abs($expectedFinal->getTimestamp() - $userFromDb->getPremiumExpiresAt()->getTimestamp());
        $this->assertLessThan(5, $diffFinal);

        $this->assertGreaterThanOrEqual(484, $this->premiumService->getDaysRemaining($userFromDb));
    }

    public function testExpireOutdatedPremiumsBatchProcess(): void
    {
        $expiredUser1 = $this->createUser('expired1@test.com', 'expired1');
        $expiredUser1->setIsPremium(true);
        $expiredUser1->setPremiumExpiresAt(new \DateTime('-2 days'));

        $expiredUser2 = $this->createUser('expired2@test.com', 'expired2');
        $expiredUser2->setIsPremium(true);
        $expiredUser2->setPremiumExpiresAt(new \DateTime('-10 days'));

        $activeUser = $this->createUser('active@test.com', 'active');
        $this->premiumService->activate($activeUser, 30);

        $nonPremiumUser = $this->createUser('free@test.com', 'freeuser');

        $this->em->flush();

        $expiredCount = $this->premiumService->expireOutdatedPremiums();

        $this->assertSame(2, $expiredCount);

        $ids = [
            'expired1' => $expiredUser1->getId(),
            'expired2' => $expiredUser2->getId(),
            'active' => $activeUser->getId(),
            'free' => $nonPremiumUser->getId(),
        ];
        $this->em->clear();

        $expired1 = $this->premiumService->getUserById($ids['expired1']);
        $expired2 = $this->premiumService->getUserById($ids['expired2']);
        $active = $this->premiumService->getUserById($ids['active']);
        $free = $this->premiumService->getUserById($ids['free']);

        $this->assertFalse($expired1->isPremium());
        $this->assertNull($expired1->getPremiumExpiresAt());
        $this->assertFalse($expired2->isPremium());
        $this->assertTrue($active->isPremium());
        $this->assertFalse($free->isPremium());
    }
}
