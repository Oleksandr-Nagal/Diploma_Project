<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\LobbyRepository;
use App\Service\SteamAchievementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/games')]
class GameController extends AbstractController
{
    #[Route('', name: 'app_games')]
    public function index(GameRepository $gameRepo): Response
    {
        return $this->render('games/index.html.twig', [
            'games' => $gameRepo->findActiveGames(),
        ]);
    }

    #[Route('/{id}', name: 'app_game_show')]
    public function show(Game $game, LobbyRepository $lobbyRepo): Response
    {
        $lobbies = $lobbyRepo->findOpenLobbies(['game' => $game->getId()]);

        return $this->render('games/show.html.twig', [
            'game' => $game,
            'lobbies' => $lobbies,
        ]);
    }

    #[Route('/{id}/sync-achievements', name: 'app_game_sync_achievements', methods: ['POST'])]
    public function syncAchievements(Game $game, SteamAchievementService $steamService): Response
    {
        $result = $steamService->syncAchievements($this->getUser(), $game);

        if ($result === 'private') {
            $this->addFlash('error', 'Не вдалося отримати досягнення. Перевірте що ваш Steam профіль та налаштування ігор публічні.');
        } elseif (is_string($result)) {
            $this->addFlash('error', $result);
        } elseif (count($result) === 0) {
            $this->addFlash('info', 'Нових досягнень не знайдено.');
        } else {
            $this->addFlash('success', 'Синхронізовано ' . count($result) . ' досягнень!');
        }

        return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
    }
}
