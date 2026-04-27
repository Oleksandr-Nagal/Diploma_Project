<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\SteamGameService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function dashboard(UserRepository $userRepo, GameRepository $gameRepo): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => count($userRepo->findAll()),
            'totalGames' => count($gameRepo->findAll()),
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    #[Route('/users/{id}/role', name: 'app_admin_user_role', methods: ['POST'])]
    public function changeRole(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $role = $request->request->get('role');
        $validRoles = ['ROLE_USER', 'ROLE_MODERATOR', 'ROLE_ADMIN'];
        if (in_array($role, $validRoles)) {
            $user->setRoles([$role]);
            $em->flush();
            $this->addFlash('success', 'Роль змінено.');
        }
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/ban', name: 'app_admin_user_ban', methods: ['POST'])]
    public function banUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->addFlash('error', 'Неможливо заблокувати адміністратора.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($user->isBanned()) {
            $user->setIsBanned(false);
            $user->setBannedUntil(null);
            $em->flush();
            $this->addFlash('success', 'Користувача розблоковано.');
            return $this->redirectToRoute('app_admin_users');
        }

        $hours = (int) $request->request->get('duration', 0);
        $user->setIsBanned(true);

        if ($hours > 0) {
            $until = (new \DateTime())->modify('+' . $hours . ' hours');
            $user->setBannedUntil($until);
            $this->addFlash('success', sprintf(
                'Користувача заблоковано до %s.',
                $until->format('d.m.Y H:i')
            ));
        } else {
            $user->setBannedUntil(null);
            $this->addFlash('success', 'Користувача заблоковано без обмеження в часі.');
        }

        $em->flush();
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/games', name: 'app_admin_games')]
    public function games(GameRepository $gameRepo): Response
    {
        return $this->render('admin/games.html.twig', [
            'games' => $gameRepo->findAll(),
        ]);
    }

    #[Route('/games/add', name: 'app_admin_game_add', methods: ['POST'])]
    public function addGame(Request $request, EntityManagerInterface $em, SteamGameService $steamService): Response
    {
        $game = new Game();
        $game->setName($request->request->get('name'));
        $game->setGenre($request->request->get('genre'));
        $game->setDescription($request->request->get('description'));
        $game->setMaxPlayers((int) $request->request->get('maxPlayers', 10));
        $game->setSlug(strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($request->request->get('name')))));

        $steamAppId = $request->request->get('steamAppId');
        if ($steamAppId) {
            $game->setSteamAppId((int) $steamAppId);
        }

        $imageUrl = $request->request->get('imageUrl');
        if (!empty($imageUrl)) {
            $game->setImageUrl($imageUrl);
        } else {
            $image = $steamService->findImage($game);
            if ($image) {
                $game->setImageUrl($image);
            }
        }

        $em->persist($game);
        $em->flush();
        $this->addFlash('success', 'Гру "' . $game->getName() . '" додано!');
        return $this->redirectToRoute('app_admin_games');
    }

    #[Route('/games/steam-search', name: 'app_admin_steam_search')]
    public function steamSearch(Request $request, SteamGameService $steamService): JsonResponse
    {
        $query = $request->query->get('q', '');
        if (strlen($query) < 2) {
            return new JsonResponse([]);
        }

        return new JsonResponse($steamService->searchGames($query));
    }

    #[Route('/games/steam-add/{appId}', name: 'app_admin_steam_add', methods: ['POST'])]
    public function steamAdd(int $appId, SteamGameService $steamService): Response
    {
        $game = $steamService->addGameFromSteam($appId);
        if ($game) {
            $this->addFlash('success', 'Гру "' . $game->getName() . '" додано зі Steam!');
        } else {
            $this->addFlash('error', 'Не вдалося додати гру зі Steam.');
        }
        return $this->redirectToRoute('app_admin_games');
    }

    #[Route('/games/steam-import-top', name: 'app_admin_steam_import', methods: ['POST'])]
    public function steamImportTop(SteamGameService $steamService): Response
    {
        $imported = $steamService->importTopGames();
        if (count($imported) > 0) {
            $this->addFlash('success', 'Імпортовано ' . count($imported) . ' ігор: ' . implode(', ', array_slice($imported, 0, 5)) . (count($imported) > 5 ? '...' : ''));
        } else {
            $this->addFlash('info', 'Всі топ-ігри вже є в каталозі.');
        }
        return $this->redirectToRoute('app_admin_games');
    }

    #[Route('/games/{id}/delete', name: 'app_admin_game_delete', methods: ['POST'])]
    public function deleteGame(Game $game, EntityManagerInterface $em): Response
    {
        $lobbiesCount = $game->getLobbies()->count();
        if ($lobbiesCount > 0) {
            $this->addFlash('error', sprintf(
                'Неможливо видалити "%s": до гри прив\'язано %d лобі. Спершу закрийте/видаліть їх.',
                $game->getName(),
                $lobbiesCount
            ));
            return $this->redirectToRoute('app_admin_games');
        }

        $eventsCount = (int) $em->createQuery(
            'SELECT COUNT(e.id) FROM App\Entity\GameEvent e WHERE e.game = :game'
        )->setParameter('game', $game)->getSingleScalarResult();

        if ($eventsCount > 0) {
            $this->addFlash('error', sprintf(
                'Неможливо видалити "%s": до гри прив\'язано %d подій.',
                $game->getName(),
                $eventsCount
            ));
            return $this->redirectToRoute('app_admin_games');
        }

        foreach ($game->getAchievements() as $achievement) {
            $em->remove($achievement);
        }

        $name = $game->getName();
        $em->remove($game);
        $em->flush();

        $this->addFlash('success', sprintf('Гру "%s" видалено.', $name));
        return $this->redirectToRoute('app_admin_games');
    }
}
