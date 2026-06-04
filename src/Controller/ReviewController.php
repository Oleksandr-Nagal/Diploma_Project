<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AvatarService;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/api/lobby/{id}/review-data', name: 'api_lobby_review_data', methods: ['GET'])]
    public function getReviewData(int $id, \App\Repository\LobbyRepository $lobbyRepo, \App\Repository\ReviewRepository $reviewRepository): JsonResponse
    {
        $lobby = $lobbyRepo->find($id);
        if (!$lobby) return $this->json(['error' => 'Lobby not found'], 404);

        $user = $this->getUser();
        if (!$user) return $this->json(['error' => 'Unauthorized'], 401);

        $isParticipant = false;
        foreach ($lobby->getMembers() as $member) {
            if ($member->getUser() === $user) {
                $isParticipant = true; break;
            }
        }
        if (!$isParticipant) return $this->json(['error' => 'Forbidden'], 403);

        $data = [];
        foreach ($lobby->getMembers() as $member) {
            $target = $member->getUser();
            if ($target === $user) continue;

            $review = $reviewRepository->findOneBy(['author' => $user, 'target' => $target]);
            
            $data[] = [
                'target_id' => $target->getId(),
                'username' => $target->getUsername(),
                'avatarHtml' => $this->renderAvatarHtml($target->getAvatar(), $target->getUsername()),
                'existingReview' => $review ? [
                    'isPositive' => $review->isPositive(),
                    'comment' => $review->getComment()
                ] : null
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/reviews/batch-upsert', name: 'api_reviews_batch_upsert', methods: ['POST'])]
    public function batchUpsert(Request $request, \App\Repository\LobbyRepository $lobbyRepository, \App\Repository\UserRepository $userRepository, ReviewService $reviewService): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) return $this->json(['error' => 'Unauthorized'], 401);

        $payload = json_decode($request->getContent(), true);
        $lobbyId = $payload['lobby_id'] ?? null;
        $reviews = $payload['reviews'] ?? [];

        if (!$lobbyId) return $this->json(['error' => 'Lobby ID is required'], 400);

        $lobby = $lobbyRepository->find($lobbyId);
        if (!$lobby) return $this->json(['error' => 'Lobby not found'], 404);

        if ($lobby->getStatus() !== 'closed') {
            return $this->json(['error' => 'Lobby is not closed'], 400);
        }

        if ($lobby->getClosedAt() && $lobby->getCreatedAt()) {
            $diff = $lobby->getClosedAt()->getTimestamp() - $lobby->getCreatedAt()->getTimestamp();
            if ($diff < 60) {
                return $this->json(['error' => 'Час існування лобі занадто малий для оцінювання (мінімум 15 хвилин).'], 400);
            }
        }


        $isParticipant = false;
        foreach ($lobby->getMembers() as $member) {
            if ($member->getUser() === $user) {
                $isParticipant = true; break;
            }
        }
        if (!$isParticipant) return $this->json(['error' => 'Forbidden'], 403);

        $lobbyUserIds = [];
        foreach ($lobby->getMembers() as $member) {
            $lobbyUserIds[] = $member->getUser()->getId();
        }

        foreach ($reviews as $reviewData) {
            $targetId = $reviewData['target_id'] ?? null;
            if (!$targetId) continue;
            if (!in_array($targetId, $lobbyUserIds)) continue;

            $target = $userRepository->find($targetId);
            if (!$target || $target === $user) continue;

            $isPositive = filter_var($reviewData['isPositive'], FILTER_VALIDATE_BOOLEAN);
            $comment = $reviewData['comment'] ?? null;

            $reviewService->upsertReview($user, $target, $isPositive, $comment, $lobby);
        }

        return $this->json(['success' => true]);
    }

    private function renderAvatarHtml(?string $avatarValue, string $username): string
    {
        $size = '40px';

        if ($avatarValue && str_starts_with($avatarValue, 'http')) {
            return '<img src="' . htmlspecialchars($avatarValue) . '" class="rounded-circle" width="40" height="40" style="object-fit:cover" alt="' . htmlspecialchars($username) . '">';
        }

        if ($avatarValue && str_starts_with($avatarValue, 'avatar:')) {
            $key = substr($avatarValue, 7);
            $data = AvatarService::getAvatar($key);
            if ($data) {
                return '<div style="width:' . $size . ';height:' . $size . ';border-radius:50%;background:linear-gradient(135deg,' . $data['color1'] . ',' . $data['color2'] . ');display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas ' . $data['icon'] . '" style="color:#fff;font-size:0.9rem;"></i></div>';
            }
        }

        $letter = mb_strtoupper(mb_substr($username, 0, 1));
        return '<div style="width:' . $size . ';height:' . $size . ';border-radius:50%;background:linear-gradient(135deg,#6c5ce7,#a855f7);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.9rem;flex-shrink:0;">' . htmlspecialchars($letter) . '</div>';
    }
}
