<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BannedController extends AbstractController
{
    #[Route('/banned', name: 'app_banned')]
    public function index(): Response
    {
        $user = $this->getUser();

        if ($user instanceof User && !$user->isBanned()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('auth/banned.html.twig', [
            'banned_user' => $user instanceof User ? $user : null,
        ]);
    }
}
