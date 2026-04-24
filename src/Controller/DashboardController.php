<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Repository\LobbyRepository;
use App\Repository\NotificationRepository;
use App\Repository\GameEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        LobbyRepository $lobbyRepo,
        GameRepository $gameRepo,
        NotificationRepository $notifRepo,
        GameEventRepository $eventRepo
    ): Response {
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'lobbies' => $lobbyRepo->findOpenLobbies(),
            'games' => $gameRepo->findActiveGames(),
            'notifications' => $notifRepo->findUnreadByUser($user),
            'events' => $eventRepo->findUpcomingEvents(5),
        ]);
    }
}
