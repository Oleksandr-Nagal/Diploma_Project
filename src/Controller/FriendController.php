<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\FriendshipRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/friends')]
class FriendController extends AbstractController
{
    #[Route('', name: 'app_friends')]
    public function index(FriendshipRepository $friendRepo): Response
    {
        $user = $this->getUser();
        return $this->render('friends/index.html.twig', [
            'friends' => $friendRepo->findAcceptedFriends($user),
            'pending' => $friendRepo->findPendingRequests($user),
            'currentUser' => $user,
        ]);
    }

    #[Route('/add/{id}', name: 'app_friend_add', methods: ['POST'])]
    public function addFriend(
        User $target,
        FriendshipRepository $friendRepo,
        EntityManagerInterface $em,
        NotificationService $notifService
    ): Response {
        $user = $this->getUser();
        if ($user === $target) {
            $this->addFlash('error', 'Ви не можете додати себе у друзі.');
            return $this->redirectToRoute('app_friends');
        }

        $existing = $friendRepo->findFriendship($user, $target);
        if ($existing) {
            if ($existing->getStatus() === 'accepted') {
                $this->addFlash('error', 'Ви вже друзі!');
            } elseif ($existing->getStatus() === 'pending') {
                if ($existing->getReceiver() === $user) {
                    $existing->setStatus('accepted');
                    $existing->setAcceptedAt(new \DateTime());
                    $em->flush();
                    $this->addFlash('success', 'Дружба прийнята!');
                } else {
                    $this->addFlash('error', 'Запит вже надіслано, очікуйте відповіді.');
                }
            } elseif ($existing->getStatus() === 'rejected') {
                $existing->setRequester($user);
                $existing->setReceiver($target);
                $existing->setStatus('pending');
                $existing->setAcceptedAt(null);
                $em->flush();
                $notifService->notifyFriendRequest($target, $user->getUsername());
                $this->addFlash('success', 'Запит дружби надіслано повторно!');
            }
            return $this->redirectToRoute('app_profile_public', ['id' => $target->getId()]);
        }

        $friendship = new Friendship();
        $friendship->setRequester($user);
        $friendship->setReceiver($target);
        $em->persist($friendship);
        $em->flush();

        $notifService->notifyFriendRequest($target, $user->getUsername());
        $this->addFlash('success', 'Запит дружби надіслано!');

        return $this->redirectToRoute('app_profile_public', ['id' => $target->getId()]);
    }

    #[Route('/accept/{id}', name: 'app_friend_accept', methods: ['POST'])]
    public function accept(Friendship $friendship, EntityManagerInterface $em, NotificationService $notifService): Response
    {
        if ($friendship->getReceiver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $friendship->setStatus('accepted');
        $friendship->setAcceptedAt(new \DateTime());
        $em->flush();

        $notifService->create(
            $friendship->getRequester(),
            'friend_request',
            $this->getUser()->getUsername() . ' прийняв вашу заявку в друзі!',
            '/friends'
        );

        $this->addFlash('success', 'Дружба прийнята!');
        return $this->redirectToRoute('app_friends');
    }

    #[Route('/reject/{id}', name: 'app_friend_reject', methods: ['POST'])]
    public function reject(Friendship $friendship, EntityManagerInterface $em): Response
    {
        if ($friendship->getReceiver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $friendship->setStatus('rejected');
        $em->flush();

        return $this->redirectToRoute('app_friends');
    }

    #[Route('/remove/{id}', name: 'app_friend_remove', methods: ['POST'])]
    public function remove(Friendship $friendship, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($friendship->getRequester() !== $user && $friendship->getReceiver() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($friendship);
        $em->flush();

        $this->addFlash('success', 'Друга видалено.');
        return $this->redirectToRoute('app_friends');
    }
}
