<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\FriendshipRepository;
use App\Repository\ReviewRepository;
use App\Repository\LobbyRepository;
use Doctrine\ORM\EntityManagerInterface;

class MatchmakingService
{
    public function __construct(
        private UserRepository $userRepo,
        private FriendshipRepository $friendRepo,
        private ReviewRepository $reviewRepo,
        private LobbyRepository $lobbyRepo,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Find recommended players for a user based on:
     * - Same games (achievements)
     * - Same language
     * - Same city / nearby
     * - Similar age range
     * - High rating
     * - Not already friends
     * - Not the user themselves
     */
    public function getRecommendations(User $user, int $limit = 12): array
    {
        $userId = $user->getId();
        $language = $user->getLanguage();
        $city = $user->getCity();
        $age = $user->getAge();

        // Get user's game IDs from achievements
        $userGameIds = [];
        foreach ($user->getAchievements() as $ach) {
            $gameId = $ach->getGame()->getId();
            $userGameIds[$gameId] = true;
        }

        // Get user's lobby game IDs
        foreach ($user->getLobbyMemberships() as $membership) {
            if ($membership->getStatus() === 'accepted') {
                $gameId = $membership->getLobby()->getGame()->getId();
                $userGameIds[$gameId] = true;
            }
        }

        // Get friend IDs to exclude
        $friendIds = [$userId];
        foreach ($user->getSentFriendRequests() as $fr) {
            $friendIds[] = $fr->getReceiver()->getId();
        }
        foreach ($user->getReceivedFriendRequests() as $fr) {
            $friendIds[] = $fr->getRequester()->getId();
        }

        // Get all active users except friends and self
        $qb = $this->userRepo->createQueryBuilder('u')
            ->where('u.id NOT IN (:excludeIds)')
            ->andWhere('u.isBanned = false')
            ->setParameter('excludeIds', $friendIds);

        $candidates = $qb->getQuery()->getResult();

        // Score each candidate
        $scored = [];
        foreach ($candidates as $candidate) {
            $score = $this->calculateScore($user, $candidate, $userGameIds);
            if ($score > 0) {
                $scored[] = [
                    'user' => $candidate,
                    'score' => $score,
                    'reasons' => $this->getMatchReasons($user, $candidate, $userGameIds),
                ];
            }
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    private function calculateScore(User $user, User $candidate, array $userGameIds): float
    {
        $score = 0;

        // Base score for having a profile
        if ($candidate->getBio()) $score += 5;
        if ($candidate->getAvatar()) $score += 3;

        // Same language: +30
        if ($user->getLanguage() && $candidate->getLanguage() && $user->getLanguage() === $candidate->getLanguage()) {
            $score += 30;
        }

        // Same city: +25
        if ($user->getCity() && $candidate->getCity() && strtolower($user->getCity()) === strtolower($candidate->getCity())) {
            $score += 25;
        }

        // Similar age (within 5 years): +20, within 10: +10
        if ($user->getAge() && $candidate->getAge()) {
            $ageDiff = abs($user->getAge() - $candidate->getAge());
            if ($ageDiff <= 3) $score += 25;
            elseif ($ageDiff <= 5) $score += 20;
            elseif ($ageDiff <= 10) $score += 10;
        }

        // Common games: +15 per game
        $candidateGameIds = [];
        foreach ($candidate->getAchievements() as $ach) {
            $candidateGameIds[$ach->getGame()->getId()] = true;
        }
        foreach ($candidate->getLobbyMemberships() as $membership) {
            if ($membership->getStatus() === 'accepted') {
                $candidateGameIds[$membership->getLobby()->getGame()->getId()] = true;
            }
        }
        $commonGames = count(array_intersect_key($userGameIds, $candidateGameIds));
        $score += $commonGames * 15;

        // High rating bonus: +0-20
        if ($candidate->getTotalReviews() >= 3) {
            $score += ($candidate->getRating() / 100) * 20;
        }

        // Has Steam connected: +5
        if ($candidate->getSteamId()) $score += 5;

        // Recently active (has lobbies): +10
        if ($candidate->getLobbyMemberships()->count() > 0) $score += 10;

        return $score;
    }

    private function getMatchReasons(User $user, User $candidate, array $userGameIds): array
    {
        $reasons = [];

        // Common games
        $commonGameNames = [];
        foreach ($candidate->getAchievements() as $ach) {
            if (isset($userGameIds[$ach->getGame()->getId()]) && !in_array($ach->getGame()->getName(), $commonGameNames)) {
                $commonGameNames[] = $ach->getGame()->getName();
            }
        }
        foreach ($candidate->getLobbyMemberships() as $m) {
            if ($m->getStatus() === 'accepted') {
                $gameId = $m->getLobby()->getGame()->getId();
                $gameName = $m->getLobby()->getGame()->getName();
                if (isset($userGameIds[$gameId]) && !in_array($gameName, $commonGameNames)) {
                    $commonGameNames[] = $gameName;
                }
            }
        }
        if (!empty($commonGameNames)) {
            $reasons[] = ['icon' => 'fa-gamepad', 'text' => 'Спільні ігри: ' . implode(', ', array_slice($commonGameNames, 0, 3))];
        }

        if ($user->getLanguage() && $candidate->getLanguage() && $user->getLanguage() === $candidate->getLanguage()) {
            $reasons[] = ['icon' => 'fa-globe', 'text' => 'Та сама мова'];
        }

        if ($user->getCity() && $candidate->getCity() && strtolower($user->getCity()) === strtolower($candidate->getCity())) {
            $reasons[] = ['icon' => 'fa-map-marker-alt', 'text' => 'Те саме місто'];
        }

        if ($user->getAge() && $candidate->getAge()) {
            $ageDiff = abs($user->getAge() - $candidate->getAge());
            if ($ageDiff <= 5) {
                $reasons[] = ['icon' => 'fa-birthday-cake', 'text' => 'Схожий вік'];
            }
        }

        if ($candidate->getTotalReviews() >= 3 && $candidate->getRating() >= 70) {
            $reasons[] = ['icon' => 'fa-star', 'text' => 'Високий рейтинг: ' . round($candidate->getRating()) . '%'];
        }

        if (empty($reasons)) {
            $reasons[] = ['icon' => 'fa-user', 'text' => 'Активний гравець'];
        }

        return $reasons;
    }
}
