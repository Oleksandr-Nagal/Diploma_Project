<?php

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use App\Service\PremiumService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PremiumControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

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

    private function createUser(string $email = 'test@example.com', string $username = 'testuser', bool $premium = false, int $days = 30): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword('hashed_password');
        $user->setIsPremium($premium);

        if ($premium) {
            $user->setPremiumExpiresAt(new \DateTime("+{$days} days"));
        }

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function testUnauthenticatedUserCannotAccessPremiumPages(): void
    {
        $this->client->request('GET', '/premium');
        $this->assertResponseRedirects();

        $this->client->request('GET', '/premium/checkout/month');
        $this->assertResponseRedirects();

        $this->client->request('GET', '/premium/manage');
        $this->assertResponseRedirects();

        $this->client->request('GET', '/premium/result');
        $this->assertResponseRedirects();
    }

    public function testPremiumPageShowsPlansForFreeUser(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/premium');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('[href*="checkout/month"]');
        $this->assertSelectorExists('[href*="checkout/quarter"]');
        $this->assertSelectorExists('[href*="checkout/year"]');
    }

    public function testCheckoutAllValidPlansAccepted(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        foreach (['month', 'quarter', 'year'] as $plan) {
            $this->client->request('GET', "/premium/checkout/{$plan}");
            $statusCode = $this->client->getResponse()->getStatusCode();
            $this->assertNotSame(404, $statusCode, "Plan '{$plan}' should be recognized");
        }
    }

    public function testCheckoutReturns404ForInvalidPlan(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/premium/checkout/lifetime');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testWebhookRejectsRequestsWithoutSignature(): void
    {
        $this->client->request('POST', '/premium/callback', [], [], [], '{}');

        $this->assertResponseStatusCodeSame(400);
    }

    public function testWebhookRejectsInvalidSignature(): void
    {
        $this->client->request('POST', '/premium/callback', [], [], [
            'HTTP_Stripe-Signature' => 't=123,v1=invalid',
        ], '{"type":"checkout.session.completed"}');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testManagePageAccessControl(): void
    {
        $freeUser = $this->createUser('free@test.com', 'freeuser');
        $this->client->loginUser($freeUser);

        $this->client->request('GET', '/premium/manage');
        $this->assertResponseRedirects('/premium');

        $premiumUser = $this->createUser('premium@test.com', 'premiumuser', true);
        $this->client->loginUser($premiumUser);

        $this->client->request('GET', '/premium/manage');
        $this->assertResponseIsSuccessful();
    }

    public function testResultPageFlowForNonPremiumUser(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/premium/result');

        $this->assertResponseRedirects('/premium');
    }

    public function testResultPageFlowForAlreadyPremiumUser(): void
    {
        $user = $this->createUser('prem@test.com', 'premuser', true);
        $this->client->loginUser($user);

        $this->client->request('GET', '/premium/result?session_id=nonexistent');

        $this->assertResponseRedirects('/premium');
    }
}
