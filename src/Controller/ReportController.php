<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Entity\Report;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/report')]
#[IsGranted('ROLE_USER')]
class ReportController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_report_user', methods: ['POST'])]
    public function reportUser(User $reportedUser, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user === $reportedUser) {
            $this->addFlash('error', 'Ви не можете поскаржитись на себе.');
            return $this->redirectToRoute('app_profile_public', ['id' => $reportedUser->getId()]);
        }

        $reason = $request->request->get('reason', '');
        if (empty(trim($reason))) {
            $this->addFlash('error', 'Вкажіть причину скарги.');
            return $this->redirectToRoute('app_profile_public', ['id' => $reportedUser->getId()]);
        }

        $report = new Report();
        $report->setReporter($user);
        $report->setReportedUser($reportedUser);
        $report->setReason($reason);

        $em->persist($report);
        $em->flush();

        $this->addFlash('success', 'Скаргу надіслано. Модератори розглянуть її найближчим часом.');
        return $this->redirectToRoute('app_profile_public', ['id' => $reportedUser->getId()]);
    }

    #[Route('/message/{id}', name: 'app_report_message', methods: ['POST'])]
    public function reportMessage(ChatMessage $message, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $reason = $request->request->get('reason', '');

        if (empty(trim($reason))) {
            $this->addFlash('error', 'Вкажіть причину скарги.');
            return $this->redirect($request->headers->get('referer', '/'));
        }

        $report = new Report();
        $report->setReporter($user);
        $report->setReportedMessage($message);
        $report->setReportedUser($message->getSender());
        $report->setReason($reason);

        $em->persist($report);
        $em->flush();

        $this->addFlash('success', 'Скаргу на повідомлення надіслано.');
        return $this->redirect($request->headers->get('referer', '/'));
    }
}
