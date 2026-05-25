<?php

namespace App\Controller;

use App\Service\PremiumService;
use App\Service\StripeService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/premium')]
class PremiumController extends AbstractController
{
    private const PLANS = [
        'month' => ['days' => 30, 'price' => 99, 'label' => '1 місяць'],
        'quarter' => ['days' => 90, 'price' => 249, 'label' => '3 місяці'],
        'year' => ['days' => 365, 'price' => 799, 'label' => '1 рік'],
    ];

    public function __construct(
        private PremiumService $premiumService,
        private StripeService $stripeService,
    ) {}

    #[Route('', name: 'app_premium')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('premium/index.html.twig', [
            'user' => $this->getUser(),
            'daysRemaining' => $this->premiumService->getDaysRemaining($this->getUser()),
        ]);
    }

    #[Route('/checkout/{plan}', name: 'app_premium_checkout', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function checkout(string $plan): Response
    {
        if (!isset(self::PLANS[$plan])) {
            throw $this->createNotFoundException();
        }

        $selectedPlan = self::PLANS[$plan];
        $user = $this->getUser();
        $orderId = 'premium_' . $user->getId() . '_' . time();

        $successUrl = $this->generateUrl('app_premium_result', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $this->generateUrl('app_premium', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $stripeUrl = $this->stripeService->createCheckoutSession(
            $orderId,
            $selectedPlan['price'],
            "GameFinder Premium — {$selectedPlan['label']}",
            $selectedPlan['days'],
            $user->getId(),
            $successUrl,
            $cancelUrl,
        );

        return $this->redirect($stripeUrl);
    }

    #[Route('/callback', name: 'app_premium_callback', methods: ['POST'])]
    public function callback(Request $request, LoggerInterface $logger): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        if (!$sigHeader) {
            return new Response('Missing signature', 400);
        }

        try {
            $event = $this->stripeService->verifyWebhook($payload, $sigHeader);
        } catch (\Exception $e) {
            $logger->warning('Stripe webhook: invalid signature', ['error' => $e->getMessage()]);
            return new Response('Invalid signature', 403);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $metadata = $session->metadata;

            $userId = (int) ($metadata->user_id ?? 0);
            $days = (int) ($metadata->days ?? 30);

            $logger->info('Stripe payment completed', ['user_id' => $userId, 'days' => $days]);

            $user = $this->premiumService->getUserById($userId);
            if ($user) {
                $this->premiumService->activate($user, $days);
                $logger->info('Premium activated', ['user_id' => $userId, 'days' => $days]);
            } else {
                $logger->error('Stripe webhook: user not found', ['user_id' => $userId]);
            }
        }

        return new Response('OK');
    }

    #[Route('/manage', name: 'app_premium_manage')]
    #[IsGranted('ROLE_USER')]
    public function manage(): Response
    {
        $user = $this->getUser();

        if (!$user->isPremium()) {
            return $this->redirectToRoute('app_premium');
        }

        return $this->render('premium/manage.html.twig', [
            'user' => $user,
            'daysRemaining' => $this->premiumService->getDaysRemaining($user),
        ]);
    }

    #[Route('/result', name: 'app_premium_result')]
    #[IsGranted('ROLE_USER')]
    public function result(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user->isPremium()) {
            $sessionId = $request->query->get('session_id');
            if ($sessionId) {
                $session = $this->stripeService->getSession($sessionId);
                if ($session && $session->payment_status === 'paid') {
                    $days = (int) ($session->metadata->days ?? 30);
                    $this->premiumService->activate($user, $days);
                    $this->addFlash('success', 'Premium успішно активовано! Дякуємо за підтримку.');
                    return $this->redirectToRoute('app_premium');
                }
            }
            $this->addFlash('info', 'Оплата обробляється. Premium буде активовано найближчим часом.');
        } else {
            $this->addFlash('success', 'Premium успішно активовано! Дякуємо за підтримку.');
        }

        return $this->redirectToRoute('app_premium');
    }
}
