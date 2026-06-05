<?php

namespace App\Tests\Integration\Service;

use App\Entity\User;
use App\Service\MatchmakingService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MatchmakingServiceTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private MatchmakingService $service;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->service = static::getContainer()->get(MatchmakingService::class);

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

    private function createUser(string $email, ?string $language = null, ?string $city = null, ?int $age = null): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername(explode('@', $email)[0]);
        $user->setPassword('hash');
        $user->setLanguage($language);
        $user->setCity($city);
        $user->setAge($age);
        $this->em->persist($user);
        return $user;
    }

    public function testGetRecommendationsExcludesSelf(): void
    {
        $user = $this->createUser('me@t.com', 'uk', 'Київ', 25);
        $this->createUser('other@t.com', 'uk', 'Київ', 25);
        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $ids = array_map(fn($r) => $r['user']->getId(), $results);
        $this->assertNotContains($user->getId(), $ids);
    }

    public function testGetRecommendationsExcludesBanned(): void
    {
        $user = $this->createUser('me@t.com', 'uk');
        $banned = $this->createUser('banned@t.com', 'uk');
        $banned->setIsBanned(true);
        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $ids = array_map(fn($r) => $r['user']->getId(), $results);
        $this->assertNotContains($banned->getId(), $ids);
    }

    public function testGetRecommendationsScoringByLanguage(): void
    {
        $user = $this->createUser('me@t.com', 'uk');
        $sameLang = $this->createUser('same@t.com', 'uk');
        $diffLang = $this->createUser('diff@t.com', 'en');
        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $this->assertNotEmpty($results);
        $this->assertSame($sameLang->getId(), $results[0]['user']->getId());
    }

    public function testGetRecommendationsReturnsReasons(): void
    {
        $user = $this->createUser('me@t.com', 'uk', 'Київ', 20);
        $this->createUser('match@t.com', 'uk', 'Київ', 21);
        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('reasons', $results[0]);
        $this->assertNotEmpty($results[0]['reasons']);
    }

    public function testGetRecommendationsRespectsLimit(): void
    {
        $user = $this->createUser('me@t.com', 'uk');
        for ($i = 0; $i < 5; $i++) {
            $this->createUser("u{$i}@t.com", 'uk');
        }
        $this->em->flush();

        $results = $this->service->getRecommendations($user, 3);

        $this->assertLessThanOrEqual(3, count($results));
    }

    public function testGetRecommendationsReturnsEmptyForNoMatches(): void
    {
        $user = $this->createUser('lonely@t.com');
        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $this->assertIsArray($results);
    }

    public function testGetRecommendationsFullScoring(): void
    {
        $game = new \App\Entity\Game();
        $game->setName('CS2');
        $game->setGenre('FPS');
        $this->em->persist($game);

        $user = $this->createUser('me@t.com', 'uk', 'Київ', 22);

        $ach1 = new \App\Entity\Achievement();
        $ach1->setUser($user);
        $ach1->setGame($game);
        $ach1->setName('First Blood');
        $this->em->persist($ach1);

        $candidate = $this->createUser('pro@t.com', 'uk', 'Київ', 23);
        $candidate->setBio('Pro gamer');
        $candidate->setAvatar('avatar:knight');
        $candidate->setSteamId('76561198000000000');
        $candidate->setRating(90.0);
        $candidate->setTotalReviews(10);

        $ach2 = new \App\Entity\Achievement();
        $ach2->setUser($candidate);
        $ach2->setGame($game);
        $ach2->setName('Ace');
        $this->em->persist($ach2);

        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $this->assertNotEmpty($results);
        $this->assertSame($candidate->getId(), $results[0]['user']->getId());
        $this->assertGreaterThan(50, $results[0]['score']);

        $reasonTexts = array_map(fn($r) => $r['text'], $results[0]['reasons']);
        $this->assertNotEmpty($reasonTexts);
    }

    public function testGetRecommendationsWithLobbyMemberships(): void
    {
        $game = new \App\Entity\Game();
        $game->setName('Dota 2');
        $game->setGenre('MOBA');
        $this->em->persist($game);

        $user = $this->createUser('me2@t.com', 'uk');

        $lobby = new \App\Entity\Lobby();
        $lobby->setTitle('Dota party');
        $lobby->setGame($game);
        $lobby->setOwner($user);
        $this->em->persist($lobby);

        $userMembership = new \App\Entity\LobbyMember();
        $userMembership->setLobby($lobby);
        $userMembership->setUser($user);
        $userMembership->setStatus('accepted');
        $this->em->persist($userMembership);

        $candidate = $this->createUser('cand@t.com', 'uk');

        $lobby2 = new \App\Entity\Lobby();
        $lobby2->setTitle('Dota ranked');
        $lobby2->setGame($game);
        $lobby2->setOwner($candidate);
        $this->em->persist($lobby2);

        $candMembership = new \App\Entity\LobbyMember();
        $candMembership->setLobby($lobby2);
        $candMembership->setUser($candidate);
        $candMembership->setStatus('accepted');
        $this->em->persist($candMembership);

        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $this->assertNotEmpty($results);
    }

    public function testGetRecommendationsHighRatingReason(): void
    {
        $user = $this->createUser('me3@t.com', 'en');
        $candidate = $this->createUser('rated@t.com', 'en');
        $candidate->setRating(85.0);
        $candidate->setTotalReviews(5);
        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $this->assertNotEmpty($results);
        $reasonTexts = array_map(fn($r) => $r['text'], $results[0]['reasons']);
        $hasRating = count(array_filter($reasonTexts, fn($t) => str_contains($t, 'рейтинг'))) > 0;
        $this->assertTrue($hasRating);
    }

    public function testGetRecommendationsAgeDiffBranches(): void
    {
        $user = $this->createUser('age@t.com', null, null, 25);
        $this->createUser('close@t.com', null, null, 27);
        $this->createUser('mid@t.com', null, null, 30);
        $this->createUser('far@t.com', null, null, 33);
        $this->createUser('toofar@t.com', null, null, 50);
        $this->em->flush();

        $results = $this->service->getRecommendations($user);

        $this->assertGreaterThanOrEqual(3, count($results));
    }
}
