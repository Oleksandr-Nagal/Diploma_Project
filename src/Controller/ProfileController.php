<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\AchievementRepository;
use App\Repository\FriendshipRepository;
use App\Repository\GameRepository;
use App\Repository\ReviewRepository;
use App\Service\AvatarService;
use App\Service\CloudinaryService;
use App\Service\ProfileThemeService;
use App\Service\SteamAchievementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function myProfile(
        AchievementRepository $achievementRepo,
        ReviewRepository $reviewRepo,
    ): Response {
        $user = $this->getUser();

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'achievements' => $achievementRepo->findByUser($user),
            'reviews' => $reviewRepo->findByTarget($user),
            'isOwn' => true,
        ]);
    }

    #[Route('/profile/sync-steam', name: 'app_profile_sync_steam', methods: ['POST'])]
    public function syncSteam(
        Request $request,
        GameRepository $gameRepo,
        SteamAchievementService $steamService
    ): Response {
        $user = $this->getUser();
        if (!$user || !$user->getSteamId()) {
            return $this->json(['ok' => false]);
        }

        $session = $request->getSession();
        $lastSync = $session->get('steam_synced_at', 0);
        if (time() - $lastSync < 3600) {
            return $this->json(['ok' => true, 'synced' => [], 'cached' => true]);
        }

        $syncResults = [];
        $games = $gameRepo->findActiveGames();
        foreach ($games as $game) {
            if ($game->getSteamAppId()) {
                $result = $steamService->syncAchievements($user, $game);
                if (is_array($result) && count($result) > 0) {
                    $syncResults[] = $game->getName() . ': +' . count($result);
                }
            }
        }

        $session->set('steam_synced_at', time());

        return $this->json([
            'ok' => true,
            'synced' => $syncResults,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $em, SteamAchievementService $steamService): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user, ['is_premium' => $user->isPremium()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedTheme = $user->getProfileTheme();
            if ($selectedTheme && ProfileThemeService::isPremiumTheme($selectedTheme) && !$user->isPremium()) {
                $user->setProfileTheme(null);
                $this->addFlash('error', 'Ця тема доступна лише для Premium-користувачів.');
            }

            $steamInput = $user->getSteamId();
            if ($steamInput && !preg_match('/^[0-9]{17}$/', $steamInput)) {
                $resolved = $steamService->resolveToSteamId64($steamInput);
                if ($resolved) {
                    $user->setSteamId($resolved);
                    $this->addFlash('success', 'Steam ID визначено: ' . $resolved);
                } else {
                    $this->addFlash('error', 'Не вдалося визначити Steam ID з цього посилання. Перевірте URL.');
                    return $this->render('profile/edit.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Профіль оновлено!');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile_public')]
    public function publicProfile(
        User $user,
        AchievementRepository $achievementRepo,
        ReviewRepository $reviewRepo,
        FriendshipRepository $friendRepo
    ): Response {
        $friendshipStatus = null;
        $currentUser = $this->getUser();
        if ($currentUser && $currentUser !== $user) {
            $friendship = $friendRepo->findFriendship($currentUser, $user);
            $friendshipStatus = $friendship ? $friendship->getStatus() : null;
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'achievements' => $achievementRepo->findByUser($user),
            'reviews' => $reviewRepo->findByTarget($user),
            'isOwn' => $user === $this->getUser(),
            'friendshipStatus' => $friendshipStatus,
        ]);
    }

    #[Route('/profile/{id}/avatar', name: 'app_profile_avatar', methods: ['POST'])]
    public function changeAvatar(Request $request, EntityManagerInterface $em, CloudinaryService $cloudinary): Response
    {
        $user = $this->getUser();
        $oldAvatar = $user->getAvatar();

        $file = $request->files->get('custom_avatar');
        if ($file && $file->isValid()) {
            if ($cloudinary->isConfigured()) {
                $url = $cloudinary->uploadAvatar($file);
                if ($url) {

                    if ($cloudinary->isCloudinaryUrl($oldAvatar)) {
                        $cloudinary->delete($oldAvatar);
                    }
                    $user->setAvatar($url);
                    $em->flush();
                    $this->addFlash('success', 'Аватар завантажено!');
                    return $this->redirectToRoute('app_profile_edit');
                }
                $this->addFlash('error', 'Помилка завантаження. Спробуйте інший файл.');
            } else {
                $this->addFlash('error', 'Cloudinary не налаштовано. Додайте CLOUDINARY_URL у змінні середовища.');
            }
            return $this->redirectToRoute('app_profile_edit');
        }

        $chosen = $request->request->get('avatar');
        if ($chosen && in_array($chosen, AvatarService::getAvatarKeys())) {

            if ($cloudinary->isCloudinaryUrl($oldAvatar)) {
                $cloudinary->delete($oldAvatar);
            }
            $user->setAvatar('avatar:' . $chosen);
            $em->flush();
            $this->addFlash('success', 'Аватар змінено!');
        }

        return $this->redirectToRoute('app_profile_edit');
    }
}
