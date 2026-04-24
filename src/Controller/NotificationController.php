<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    #[Route('', name: 'app_notifications')]
    public function index(NotificationRepository $notifRepo, NotificationService $notifService): Response
    {
        $user = $this->getUser();
        $notifications = $notifRepo->findByUser($user);

        $notifService->markAllAsRead($user);

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/mark-read', name: 'app_notifications_read_all', methods: ['POST'])]
    public function markAllRead(NotificationService $notifService): Response
    {
        $notifService->markAllAsRead($this->getUser());
        return $this->redirectToRoute('app_notifications');
    }

    #[Route('/count', name: 'app_notifications_count')]
    public function count(NotificationRepository $notifRepo): JsonResponse
    {
        return new JsonResponse([
            'count' => $notifRepo->countUnread($this->getUser()),
        ]);
    }
}
