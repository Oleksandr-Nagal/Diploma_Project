<?php

namespace App\Controller;

use App\Service\MatchmakingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MatchmakingController extends AbstractController
{
    #[Route('/find-players', name: 'app_find_players')]
    public function index(MatchmakingService $matchmaking): Response
    {
        $user = $this->getUser();
        $recommendations = $matchmaking->getRecommendations($user);

        $profileComplete = $this->getProfileCompleteness($user);

        return $this->render('matchmaking/index.html.twig', [
            'recommendations' => $recommendations,
            'profileComplete' => $profileComplete,
        ]);
    }

    private function getProfileCompleteness($user): array
    {
        $missing = [];
        if (!$user->getLanguage()) $missing[] = 'мову';
        if (!$user->getCity()) $missing[] = 'місто';
        if (!$user->getAge()) $missing[] = 'вік';
        if (!$user->getSteamId()) $missing[] = 'Steam ID';
        if (!$user->getBio()) $missing[] = 'біографію';

        return [
            'percent' => max(0, 100 - count($missing) * 20),
            'missing' => $missing,
        ];
    }
}
