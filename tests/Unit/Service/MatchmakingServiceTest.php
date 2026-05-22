<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\FriendshipRepository;
use App\Repository\LobbyRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\MatchmakingService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MatchmakingServiceTest extends TestCase
{
    private UserRepository&MockObject $userRepo;
    private MatchmakingService $service;

    protected function setUp(): void
    {
        $this->userRepo = $this->createMock(UserRepository::class);
        $friendRepo = $this->createMock(FriendshipRepository::class);
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $lobbyRepo = $this->createMock(LobbyRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $this->service = new MatchmakingService(
            $this->userRepo,
            $friendRepo,
            $reviewRepo,
            $lobbyRepo,
            $em
        );
    }

    public function testCalculateScoreGivesPointsForSameLanguage(): void
    {
        $user = $this->createUser(1, 'uk', null, null);
        $candidate = $this->createUser(2, 'uk', null, null);

        $score = $this->invokeCalculateScore($user, $candidate, []);

        $this->assertGreaterThanOrEqual(30, $score);
    }

    public function testCalculateScoreGivesZeroForDifferentLanguage(): void
    {
        $user = $this->createUser(1, 'uk', null, null);
        $candidate = $this->createUser(2, 'en', null, null);

        $score = $this->invokeCalculateScore($user, $candidate, []);
        $scoreSameLang = $this->invokeCalculateScore($user, $this->createUser(3, 'uk', null, null), []);

        $this->assertLessThan($scoreSameLang, $score);
    }

    public function testCalculateScoreGivesPointsForSameCity(): void
    {
        $user = $this->createUser(1, null, 'Київ', null);
        $candidate = $this->createUser(2, null, 'Київ', null);

        $score = $this->invokeCalculateScore($user, $candidate, []);

        $this->assertGreaterThanOrEqual(25, $score);
    }

    public function testCalculateScoreGivesPointsForSimilarAge(): void
    {
        $user = $this->createUser(1, null, null, 25);
        $candidateClose = $this->createUser(2, null, null, 27);
        $candidateFar = $this->createUser(3, null, null, 45);

        $scoreClose = $this->invokeCalculateScore($user, $candidateClose, []);
        $scoreFar = $this->invokeCalculateScore($user, $candidateFar, []);

        $this->assertGreaterThan($scoreFar, $scoreClose);
    }

    public function testCalculateScoreGivesPointsForSteamConnection(): void
    {
        $user = $this->createUser(1, null, null, null);

        $candidateWithSteam = $this->createUser(2, null, null, null);
        $candidateWithSteam->setSteamId('76561198000000000');

        $candidateWithout = $this->createUser(3, null, null, null);

        $scoreWith = $this->invokeCalculateScore($user, $candidateWithSteam, []);
        $scoreWithout = $this->invokeCalculateScore($user, $candidateWithout, []);

        $this->assertGreaterThan($scoreWithout, $scoreWith);
    }

    public function testCalculateScoreGivesPointsForBio(): void
    {
        $user = $this->createUser(1, null, null, null);

        $candidateWithBio = $this->createUser(2, null, null, null);
        $candidateWithBio->setBio('I am a gamer');

        $candidateWithout = $this->createUser(3, null, null, null);

        $scoreWith = $this->invokeCalculateScore($user, $candidateWithBio, []);
        $scoreWithout = $this->invokeCalculateScore($user, $candidateWithout, []);

        $this->assertGreaterThan($scoreWithout, $scoreWith);
    }

    public function testCalculateScoreGivesPointsForHighRating(): void
    {
        $user = $this->createUser(1, null, null, null);

        $candidateHighRating = $this->createUser(2, null, null, null);
        $candidateHighRating->setRating(90.0);
        $candidateHighRating->setTotalReviews(10);

        $candidateLowRating = $this->createUser(3, null, null, null);
        $candidateLowRating->setRating(20.0);
        $candidateLowRating->setTotalReviews(10);

        $scoreHigh = $this->invokeCalculateScore($user, $candidateHighRating, []);
        $scoreLow = $this->invokeCalculateScore($user, $candidateLowRating, []);

        $this->assertGreaterThan($scoreLow, $scoreHigh);
    }

    public function testGetMatchReasonsIncludesLanguage(): void
    {
        $user = $this->createUser(1, 'uk', null, null);
        $candidate = $this->createUser(2, 'uk', null, null);

        $reasons = $this->invokeGetMatchReasons($user, $candidate, []);

        $reasonTexts = array_map(fn($r) => $r['text'], $reasons);
        $this->assertContains('Та сама мова', $reasonTexts);
    }

    public function testGetMatchReasonsIncludesCity(): void
    {
        $user = $this->createUser(1, null, 'Львів', null);
        $candidate = $this->createUser(2, null, 'Львів', null);

        $reasons = $this->invokeGetMatchReasons($user, $candidate, []);

        $reasonTexts = array_map(fn($r) => $r['text'], $reasons);
        $this->assertContains('Те саме місто', $reasonTexts);
    }

    public function testGetMatchReasonsIncludesAge(): void
    {
        $user = $this->createUser(1, null, null, 22);
        $candidate = $this->createUser(2, null, null, 24);

        $reasons = $this->invokeGetMatchReasons($user, $candidate, []);

        $reasonTexts = array_map(fn($r) => $r['text'], $reasons);
        $this->assertContains('Схожий вік', $reasonTexts);
    }

    public function testGetMatchReasonsFallbackToActivePlayer(): void
    {
        $user = $this->createUser(1, 'uk', 'Київ', 25);
        $candidate = $this->createUser(2, 'en', 'Львів', 45);

        $reasons = $this->invokeGetMatchReasons($user, $candidate, []);

        $reasonTexts = array_map(fn($r) => $r['text'], $reasons);
        $this->assertContains('Активний гравець', $reasonTexts);
    }

    private function createUser(int $id, ?string $language, ?string $city, ?int $age): User
    {
        $user = new User();
        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, $id);

        $user->setEmail("user{$id}@test.com");
        $user->setUsername("user{$id}");
        $user->setLanguage($language);
        $user->setCity($city);
        $user->setAge($age);

        return $user;
    }

    private function invokeCalculateScore(User $user, User $candidate, array $userGameIds): float
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateScore');

        return $method->invoke($this->service, $user, $candidate, $userGameIds);
    }

    private function invokeGetMatchReasons(User $user, User $candidate, array $userGameIds): array
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getMatchReasons');

        return $method->invoke($this->service, $user, $candidate, $userGameIds);
    }
}
