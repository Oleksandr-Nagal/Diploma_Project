<?php

namespace App\Controller;

use App\Entity\Lobby;
use App\Entity\LobbyMember;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

class VoiceRoomController extends AbstractController
{
    #[Route('/lobby/{id}/voice/join', name: 'app_lobby_voice_join', methods: ['POST'])]
    public function join(Lobby $lobby, HubInterface $hub): JsonResponse
    {
        if (!$this->isLobbyParticipant($lobby)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $topic = $this->topic($lobby->getId());
        $token = $hub->getFactory()->create(subscribe: [$topic], publish: [$topic]);

        return new JsonResponse([
            'status' => 'ok',
            'mercure_url' => $hub->getPublicUrl(),
            'mercure_token' => $token,
            'topic' => $topic,
            'room_id' => $lobby->getId(),
            'user_id' => $this->getUser()->getId(),
            'user_name' => $this->getUser()->getUsername(),
            'ice_servers' => $this->getIceServers(),
        ]);
    }

    #[Route('/lobby/{id}/voice/signal', name: 'app_lobby_voice_signal', methods: ['POST'])]
    public function signal(Lobby $lobby, Request $request, HubInterface $hub): JsonResponse
    {
        if (!$this->isLobbyParticipant($lobby)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Forbidden'], 403);
        }

        $payload = $request->toArray();
        $payload['from'] = $this->getUser()->getId();
        $payload['from_name'] = $this->getUser()->getUsername();

        $hub->publish(new Update(
            $this->topic($lobby->getId()),
            json_encode($payload),
        ));

        return new JsonResponse(['status' => 'ok']);
    }

    private function getIceServers(): array
    {
        $servers = [
            ['urls' => 'stun:stun.l.google.com:19302'],
            ['urls' => 'stun:stun1.l.google.com:19302'],
        ];

        $turnUrl = $_ENV['TURN_SERVER_URL'] ?? null;
        $turnUser = $_ENV['TURN_SERVER_USERNAME'] ?? null;
        $turnCred = $_ENV['TURN_SERVER_CREDENTIAL'] ?? null;

        if ($turnUrl && $turnUser && $turnCred) {
            $servers[] = [
                'urls' => $turnUrl,
                'username' => $turnUser,
                'credential' => $turnCred,
            ];
        }

        return $servers;
    }

    private function topic(int $lobbyId): string
    {
        return sprintf('/voice/lobby/%d', $lobbyId);
    }

    private function isLobbyParticipant(Lobby $lobby): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }
        if ($lobby->getOwner() && $lobby->getOwner()->getId() === $user->getId()) {
            return true;
        }
        foreach ($lobby->getMembers() as $member) {
            /** @var LobbyMember $member */
            if ($member->getUser() && $member->getUser()->getId() === $user->getId() && $member->getStatus() === 'accepted') {
                return true;
            }
        }
        return false;
    }
}
