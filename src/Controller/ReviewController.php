<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/review/{id}', name: 'app_review_create', methods: ['POST'])]
    public function create(User $target, Request $request, ReviewService $reviewService): Response
    {
        $user = $this->getUser();
        if ($user === $target) {
            $this->addFlash('error', 'Ви не можете оцінити себе.');
            return $this->redirectToRoute('app_profile_public', ['id' => $target->getId()]);
        }

        $isPositive = $request->request->get('rating') === 'positive';
        $comment = $request->request->get('comment');

        $reviewService->createReview($user, $target, $isPositive, $comment);
        $this->addFlash('success', 'Відгук надіслано!');

        return $this->redirectToRoute('app_profile_public', ['id' => $target->getId()]);
    }
}
