<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Entity\GameEvent;
use App\Entity\Lobby;
use App\Entity\Report;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReportRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\ReviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/moderator')]
#[IsGranted('ROLE_MODERATOR')]
class ModeratorController extends AbstractController
{
    #[Route('', name: 'app_moderator')]
    public function dashboard(ReportRepository $reportRepo, UserRepository $userRepo): Response
    {
        $pendingReports = $reportRepo->findPending();
        $allReports = $reportRepo->findAllSorted();
        $totalUsers = count($userRepo->findAll());

        $recentActions = array_filter($allReports, fn(Report $r) => $r->getStatus() !== 'pending');
        $recentActions = array_slice($recentActions, 0, 10);

        return $this->render('moderator/dashboard.html.twig', [
            'pendingCount' => count($pendingReports),
            'totalUsers' => $totalUsers,
            'totalReports' => count($allReports),
            'recentActions' => $recentActions,
        ]);
    }

    #[Route('/reports', name: 'app_moderator_reports')]
    public function reports(ReportRepository $reportRepo): Response
    {
        return $this->render('moderator/reports.html.twig', [
            'reports' => $reportRepo->findAllSorted(),
        ]);
    }

    #[Route('/reports/{id}/resolve', name: 'app_moderator_report_resolve', methods: ['POST'])]
    public function resolveReport(Report $report, Request $request, EntityManagerInterface $em): Response
    {
        $note = $request->request->get('note', '');
        $report->setStatus('resolved');
        $report->setModeratorNote($note);
        $report->setResolvedAt(new \DateTime());
        $report->setResolvedBy($this->getUser());
        $em->flush();

        $this->addFlash('success', 'Скаргу вирішено.');
        return $this->redirectToRoute('app_moderator_reports');
    }

    #[Route('/chat/{id}/delete', name: 'app_moderator_delete_message', methods: ['POST'])]
    public function deleteMessage(ChatMessage $message, EntityManagerInterface $em): Response
    {
        $message->setContent('[Видалено модератором]');
        $message->setAttachmentUrl(null);
        $em->flush();

        $this->addFlash('success', 'Повідомлення видалено.');
        return $this->redirectToRoute('app_moderator');
    }

    #[Route('/lobby/{id}/close', name: 'app_moderator_close_lobby', methods: ['POST'])]
    public function closeLobby(Lobby $lobby, EntityManagerInterface $em): Response
    {
        $lobby->setStatus('closed');
        $em->flush();

        $this->addFlash('success', 'Лобі "' . $lobby->getTitle() . '" закрито.');
        return $this->redirectToRoute('app_moderator');
    }

    #[Route('/event/{id}/close', name: 'app_moderator_close_event', methods: ['POST'])]
    public function closeEvent(GameEvent $event, EntityManagerInterface $em): Response
    {
        $em->remove($event);
        $em->flush();

        $this->addFlash('success', 'Подію "' . $event->getTitle() . '" видалено.');
        return $this->redirectToRoute('app_moderator');
    }

    #[Route('/users/{id}/ban', name: 'app_moderator_ban', methods: ['POST'])]
    public function tempBan(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->addFlash('error', 'Неможливо заблокувати адміністратора.');
            return $this->redirectToRoute('app_moderator_users');
        }

        $hours = (int) $request->request->get('duration', $request->request->get('hours', 24));
        if ($hours <= 0) {
            $hours = 24;
        }

        $bannedUntil = new \DateTime('+' . $hours . ' hours');

        $user->setIsBanned(true);
        $user->setBannedUntil($bannedUntil);
        $em->flush();

        $this->addFlash('success', sprintf(
            'Користувача %s заблоковано до %s.',
            $user->getUsername(),
            $bannedUntil->format('d.m.Y H:i')
        ));
        return $this->redirectToRoute('app_moderator_users');
    }

    #[Route('/users/{id}/unban', name: 'app_moderator_unban', methods: ['POST'])]
    public function unban(User $user, EntityManagerInterface $em): Response
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->addFlash('error', 'Неможливо керувати блокуванням адміністратора.');
            return $this->redirectToRoute('app_moderator_users');
        }

        $user->setIsBanned(false);
        $user->setBannedUntil(null);
        $em->flush();

        $this->addFlash('success', 'Користувача ' . $user->getUsername() . ' розблоковано.');
        return $this->redirectToRoute('app_moderator_users');
    }

    #[Route('/review/{id}/delete', name: 'app_moderator_delete_review', methods: ['POST'])]
    public function deleteReview(Review $review, EntityManagerInterface $em, ReviewService $reviewService): Response
    {
        $target = $review->getTarget();
        $em->remove($review);
        $em->flush();

        $reviewService->updateUserRating($target);
        $em->flush();

        $this->addFlash('success', 'Відгук видалено, рейтинг перераховано.');
        return $this->redirectToRoute('app_moderator');
    }

    #[Route('/users', name: 'app_moderator_users')]
    public function users(UserRepository $userRepo): Response
    {
        return $this->render('moderator/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }
}
