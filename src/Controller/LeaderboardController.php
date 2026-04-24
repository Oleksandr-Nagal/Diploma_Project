<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LeaderboardController extends AbstractController
{
    #[Route('/leaderboard', name: 'app_leaderboard')]
    public function index(UserRepository $userRepo): Response
    {
        return $this->render('leaderboard/index.html.twig', [
            'leaders' => $userRepo->getLeaderboard(),
        ]);
    }
}
