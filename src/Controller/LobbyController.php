<?php

namespace App\Controller;

use App\Entity\Lobby;
use App\Entity\LobbyMember;
use App\Form\LobbyType;
use App\Repository\GameRepository;
use App\Repository\LobbyRepository;
use App\Repository\LobbyMemberRepository;
use App\Service\LobbyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lobby')]
class LobbyController extends AbstractController
{
    #[Route('', name: 'app_lobbies')]
    public function index(Request $request, LobbyRepository $lobbyRepo, GameRepository $gameRepo): Response
    {
        $filters = [
            'search' => $request->query->get('search'),
            'game' => $request->query->get('game'),
            'city' => $request->query->get('city'),
            'language' => $request->query->get('language'),
            'skillLevel' => $request->query->get('skillLevel'),
            'voiceChat' => $request->query->getBoolean('voiceChat'),
            'genre' => $request->query->get('genre'),
        ];

        return $this->render('lobby/index.html.twig', [
            'lobbies' => $lobbyRepo->findOpenLobbies($filters),
            'games' => $gameRepo->findActiveGames(),
            'filters' => $filters,
        ]);
    }

    #[Route('/scheduled', name: 'app_lobbies_scheduled')]
    public function scheduled(LobbyRepository $lobbyRepo): Response
    {
        return $this->render('lobby/scheduled.html.twig', [
            'lobbies' => $lobbyRepo->findScheduledLobbies(),
        ]);
    }

    #[Route('/create', name: 'app_lobby_create')]
    public function create(Request $request, LobbyService $lobbyService): Response
    {
        $lobby = new Lobby();
        $form = $this->createForm(LobbyType::class, $lobby);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lobbyService->createLobby($this->getUser(), $lobby);
            $this->addFlash('success', 'Лобі створено!');
            return $this->redirectToRoute('app_lobby_show', ['id' => $lobby->getId()]);
        }

        return $this->render('lobby/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_lobby_show')]
    public function show(Lobby $lobby): Response
    {
        return $this->render('lobby/show.html.twig', [
            'lobby' => $lobby,
            'isMember' => $this->isUserMember($lobby),
            'isPending' => $this->isUserPending($lobby),
            'isOwner' => $lobby->getOwner() === $this->getUser(),
        ]);
    }

    #[Route('/{id}/join', name: 'app_lobby_join', methods: ['POST'])]
    public function join(Lobby $lobby, LobbyService $lobbyService): Response
    {
        $member = $lobbyService->joinLobby($this->getUser(), $lobby);
        if ($member) {
            $this->addFlash('success', $lobby->isPrivate() ? 'Заявку надіслано!' : 'Ви приєдналися до лобі!');
        } else {
            $this->addFlash('error', 'Не вдалося приєднатися до лобі.');
        }
        return $this->redirectToRoute('app_lobby_show', ['id' => $lobby->getId()]);
    }

    #[Route('/{id}/leave', name: 'app_lobby_leave', methods: ['POST'])]
    public function leave(Lobby $lobby, LobbyService $lobbyService): Response
    {
        $lobbyService->leaveLobby($this->getUser(), $lobby);
        $this->addFlash('success', 'Ви покинули лобі.');
        return $this->redirectToRoute('app_lobbies');
    }

    #[Route('/{id}/accept/{memberId}', name: 'app_lobby_accept', methods: ['POST'])]
    public function acceptMember(Lobby $lobby, int $memberId, LobbyService $lobbyService, LobbyMemberRepository $memberRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($lobby->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $member = $memberRepo->find($memberId);
        if ($member && $member->getLobby() === $lobby) {
            $lobbyService->acceptMember($member);
            $this->addFlash('success', 'Гравця прийнято!');
        }
        return $this->redirectToRoute('app_lobby_show', ['id' => $lobby->getId()]);
    }

    #[Route('/{id}/reject/{memberId}', name: 'app_lobby_reject', methods: ['POST'])]
    public function rejectMember(Lobby $lobby, int $memberId, LobbyService $lobbyService, LobbyMemberRepository $memberRepo): Response
    {
        if ($lobby->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $member = $memberRepo->find($memberId);
        if ($member && $member->getLobby() === $lobby) {
            $lobbyService->rejectMember($member);
        }
        return $this->redirectToRoute('app_lobby_show', ['id' => $lobby->getId()]);
    }

    #[Route('/{id}/close', name: 'app_lobby_close', methods: ['POST'])]
    public function close(Lobby $lobby, EntityManagerInterface $em): Response
    {
        if ($lobby->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $lobby->setStatus('closed');
        $em->flush();
        $this->addFlash('success', 'Лобі закрито.');
        return $this->redirectToRoute('app_lobbies');
    }

    #[Route('/{id}/status', name: 'app_lobby_status')]
    public function status(Lobby $lobby): JsonResponse
    {
        $user = $this->getUser();
        $memberStatus = 'none';
        foreach ($lobby->getMembers() as $m) {
            if ($m->getUser() === $user) {
                $memberStatus = $m->getStatus();
                break;
            }
        }

        return new JsonResponse([
            'lobbyStatus' => $lobby->getStatus(),
            'memberStatus' => $memberStatus,
            'memberCount' => $lobby->getCurrentMemberCount(),
            'maxMembers' => $lobby->getMaxMembers(),
        ]);
    }

    private function isUserMember(Lobby $lobby): bool
    {
        foreach ($lobby->getMembers() as $m) {
            if ($m->getUser() === $this->getUser() && $m->getStatus() === 'accepted') {
                return true;
            }
        }
        return false;
    }

    private function isUserPending(Lobby $lobby): bool
    {
        foreach ($lobby->getMembers() as $m) {
            if ($m->getUser() === $this->getUser() && $m->getStatus() === 'pending') {
                return true;
            }
        }
        return false;
    }
}
