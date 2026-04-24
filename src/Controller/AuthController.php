<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('auth/login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Реєстрація успішна! Увійдіть до свого акаунту.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}

    #[Route('/oauth/google', name: 'oauth_google_start')]
    public function googleStart(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('google')->redirect(['email', 'profile'], []);
    }

    #[Route('/oauth/google/check', name: 'oauth_google_check')]
    public function googleCheck(): Response
    {
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/oauth/discord', name: 'oauth_discord_start')]
    public function discordStart(Request $request): Response
    {
        $clientId = $_ENV['DISCORD_CLIENT_ID'];
        $redirectUri = $request->getSchemeAndHttpHost() . '/oauth/discord/check';
        $scope = 'identify email';

        $url = 'https://discord.com/api/oauth2/authorize?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => $scope,
            ]);

        return $this->redirect($url);
    }

    #[Route('/oauth/discord/check', name: 'oauth_discord_check')]
    public function discordCheck(
        Request $request,
        \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient,
        EntityManagerInterface $em,
        \App\Repository\UserRepository $userRepo,
        \Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface $userAuthenticator,
        \App\Security\DiscordAuthenticator $discordAuth
    ): Response {
        $code = $request->query->get('code');
        if (!$code) {
            $this->addFlash('error', 'Discord авторизація не вдалася.');
            return $this->redirectToRoute('app_login');
        }

        $tokenResponse = $httpClient->request('POST', 'https://discord.com/api/oauth2/token', [
            'body' => [
                'client_id' => $_ENV['DISCORD_CLIENT_ID'],
                'client_secret' => $_ENV['DISCORD_CLIENT_SECRET'],
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $request->getSchemeAndHttpHost() . '/oauth/discord/check',
            ],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ]);

        $tokenData = $tokenResponse->toArray();
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            $this->addFlash('error', 'Discord: не вдалося отримати токен.');
            return $this->redirectToRoute('app_login');
        }

        $userResponse = $httpClient->request('GET', 'https://discord.com/api/users/@me', [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
        ]);

        $data = $userResponse->toArray();
        $discordId = $data['id'];
        $email = $data['email'] ?? null;
        $username = $data['username'] ?? 'discord_' . $discordId;

        // Find or create user
        $user = $userRepo->findByDiscordId($discordId);
        if (!$user && $email) {
            $user = $userRepo->findOneBy(['email' => $email]);
        }

        if (!$user) {
            $user = new User();
            $user->setEmail($email ?? $discordId . '@discord.local');
            $user->setUsername($username);
            $user->setDiscordId($discordId);
            if (isset($data['avatar'])) {
                $user->setAvatar('https://cdn.discordapp.com/avatars/' . $discordId . '/' . $data['avatar'] . '.png');
            }
            $user->setIsVerified(true);
            $em->persist($user);
            $em->flush();
        } else {
            $user->setDiscordId($discordId);
            $em->flush();
        }

        return $userAuthenticator->authenticateUser($user, $discordAuth, $request);
    }
}
