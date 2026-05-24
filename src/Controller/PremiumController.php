<?php

namespace App\Controller;

use App\Service\LiqPayService;
use App\Service\PremiumService;
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
    public function __construct(
        private PremiumService $premiumService,
        private LiqPayService $liqPayService,
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
        $plans = [
            'month' => ['days' => 30, 'price' => 99, 'label' => '1 місяць'],
            'quarter' => ['days' => 90, 'price' => 249, 'label' => '3 місяці'],
            'year' => ['days' => 365, 'price' => 799, 'label' => '1 рік'],
        ];

        if (!isset($plans[$plan])) {
            throw $this->createNotFoundException();
        }

        $selectedPlan = $plans[$plan];
        $user = $this->getUser();
        $orderId = 'premium_' . $user->getId() . '_' . time();

        $resultUrl = $this->generateUrl('app_premium_result', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $serverUrl = $this->generateUrl('app_premium_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $paymentForm = $this->liqPayService->createPaymentForm(
            $orderId,
            $selectedPlan['price'],
            "GameFinder Premium — {$selectedPlan['label']}",
            $resultUrl,
            $serverUrl,
        );

        return $this->render('premium/checkout.html.twig', [
            'plan' => $selectedPlan,
            'planKey' => $plan,
            'paymentForm' => $paymentForm,
        ]);
    }

    #[Route('/callback', name: 'app_premium_callback', methods: ['POST'])]
    public function callback(Request $request, LoggerInterface $logger): Response
    {
        $data = $request->request->get('data');
        $signature = $request->request->get('signature');

        if (!$data || !$signature) {
            return new Response('Missing data', 400);
        }

        if (!$this->liqPayService->verifyCallback($data, $signature)) {
            $logger->warning('LiqPay callback: invalid signature');
            return new Response('Invalid signature', 403);
        }

        $decoded = $this->liqPayService->decodeData($data);
        $status = $decoded['status'] ?? '';
        $orderId = $decoded['order_id'] ?? '';

        $logger->info('LiqPay callback received', ['status' => $status, 'order_id' => $orderId]);

        if (!in_array($status, ['success', 'sandbox'])) {
            return new Response('OK');
        }

        $parts = explode('_', $orderId);
        if (count($parts) < 3 || $parts[0] !== 'premium') {
            return new Response('Invalid order_id', 400);
        }

        $userId = (int) $parts[1];
        $user = $this->premiumService->getUserById($userId);

        if (!$user) {
            $logger->error('LiqPay callback: user not found', ['user_id' => $userId]);
            return new Response('User not found', 404);
        }

        $amount = (float) ($decoded['amount'] ?? 0);
        $days = $this->getDaysByAmount($amount);

        $this->premiumService->activate($user, $days);
        $logger->info('Premium activated', ['user_id' => $userId, 'days' => $days]);

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
    public function result(): Response
    {
        $user = $this->getUser();

        if ($user->isPremium()) {
            $this->addFlash('success', 'Premium успішно активовано! Дякуємо за підтримку.');
        } else {
            $this->addFlash('info', 'Оплата обробляється. Premium буде активовано найближчим часом.');
        }

        return $this->redirectToRoute('app_premium');
    }

    private function getDaysByAmount(float $amount): int
    {
        return match (true) {
            $amount >= 799 => 365,
            $amount >= 249 => 90,
            default => 30,
        };
    }
}
