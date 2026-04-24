<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameEvent;
use App\Repository\GameEventRepository;
use App\Repository\GameRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/events')]
class EventController extends AbstractController
{
    #[Route('', name: 'app_events')]
    public function index(GameEventRepository $eventRepo): Response
    {
        return $this->render('events/index.html.twig', [
            'events' => $eventRepo->findUpcomingEvents(),
        ]);
    }

    #[Route('/create', name: 'app_event_create')]
    public function create(Request $request, EntityManagerInterface $em, GameRepository $gameRepo): Response
    {
        if ($request->isMethod('POST')) {
            $event = new GameEvent();
            $event->setTitle($request->request->get('title'));
            $event->setDescription($request->request->get('description'));
            $event->setOrganizer($this->getUser());
            $event->setGame($em->getReference(Game::class, $request->request->get('game_id')));
            $event->setStartAt(new \DateTime($request->request->get('start_at')));
            $event->setMaxParticipants((int) $request->request->get('max_participants', 10));

            if ($endAt = $request->request->get('end_at')) {
                $event->setEndAt(new \DateTime($endAt));
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Подію створено!');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('events/create.html.twig', [
            'games' => $gameRepo->findActiveGames(),
        ]);
    }

    #[Route('/{id}', name: 'app_event_show')]
    public function show(GameEvent $event): Response
    {
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'isParticipant' => $event->getParticipants()->contains($this->getUser()),
            'isOrganizer' => $event->getOrganizer() === $this->getUser(),
        ]);
    }

    #[Route('/{id}/join', name: 'app_event_join', methods: ['POST'])]
    public function join(GameEvent $event, EntityManagerInterface $em): Response
    {
        if ($event->getParticipants()->count() >= $event->getMaxParticipants()) {
            $this->addFlash('error', 'Подія вже повна.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $event->addParticipant($this->getUser());
        $em->flush();
        $this->addFlash('success', 'Ви приєдналися до події!');

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/{id}/leave', name: 'app_event_leave', methods: ['POST'])]
    public function leave(GameEvent $event, EntityManagerInterface $em): Response
    {
        $event->removeParticipant($this->getUser());
        $em->flush();
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}
