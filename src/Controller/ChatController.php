<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Entity\GameEvent;
use App\Entity\Lobby;
use App\Entity\User;
use App\Repository\ChatMessageRepository;
use App\Service\CloudinaryService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    #[Route('/lobby/{id}/chat', name: 'app_lobby_chat')]
    public function lobbyChat(Lobby $lobby, ChatMessageRepository $chatRepo): Response
    {
        $messages = $chatRepo->findLobbyMessages($lobby);
        return $this->render('chat/lobby.html.twig', [
            'lobby' => $lobby,
            'messages' => array_reverse($messages),
        ]);
    }

    #[Route('/lobby/{id}/chat/send', name: 'app_lobby_chat_send', methods: ['POST'])]
    public function sendLobbyMessage(Lobby $lobby, Request $request, EntityManagerInterface $em, CloudinaryService $cloudinary): Response
    {
        $content = trim($request->request->get('message', ''));
        if (empty($content) && !$request->files->get('attachment')) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'error', 'message' => 'Empty'], 400);
            }
            return $this->redirectToRoute('app_lobby_chat', ['id' => $lobby->getId()]);
        }

        $message = new ChatMessage();
        $message->setSender($this->getUser());
        $message->setLobby($lobby);
        $message->setContent($content ?: '');

        $file = $request->files->get('attachment');
        if ($file && $file->isValid()) {
            $ext = $file->guessExtension() ?? 'bin';
            $url = $cloudinary->isConfigured() ? $cloudinary->upload($file, 'gamefinder/chat') : null;

            if (!$url) {
                // Fallback to local upload
                $filename = uniqid() . '.' . $ext;
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/chat';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $file->move($uploadDir, $filename);
                $url = '/uploads/chat/' . $filename;
            }

            $message->setAttachmentUrl($url);
            $message->setType($this->isImage($ext) ? 'image' : 'file');
            if (empty($content)) {
                $message->setContent($this->isImage($ext) ? '[Зображення]' : '[Файл]');
            }
        }

        $em->persist($message);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => 'ok',
                'message' => $this->formatMessage($message),
            ]);
        }

        return $this->redirectToRoute('app_lobby_chat', ['id' => $lobby->getId()]);
    }

    #[Route('/lobby/{id}/chat/poll', name: 'app_lobby_chat_poll')]
    public function lobbyPoll(Lobby $lobby, Request $request, ChatMessageRepository $chatRepo): JsonResponse
    {
        $afterId = (int) $request->query->get('after', 0);

        $qb = $chatRepo->createQueryBuilder('m')
            ->where('m.lobby = :lobby')
            ->andWhere('m.isPrivate = false')
            ->setParameter('lobby', $lobby)
            ->orderBy('m.createdAt', 'ASC');

        if ($afterId > 0) {
            $qb->andWhere('m.id > :afterId')->setParameter('afterId', $afterId);
        }

        $messages = $qb->getQuery()->getResult();

        return new JsonResponse([
            'messages' => array_map(fn($m) => $this->formatMessage($m), $messages),
        ]);
    }

    #[Route('/messages', name: 'app_messages')]
    public function conversations(ChatMessageRepository $chatRepo): Response
    {
        $messages = $chatRepo->getConversationList($this->getUser());
        $conversations = [];
        foreach ($messages as $msg) {
            $other = $msg->getSender() === $this->getUser() ? $msg->getRecipient() : $msg->getSender();
            if ($other && !isset($conversations[$other->getId()])) {
                $conversations[$other->getId()] = [
                    'user' => $other,
                    'lastMessage' => $msg,
                ];
            }
        }

        return $this->render('chat/conversations.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/messages/{id}', name: 'app_private_chat')]
    public function privateChat(User $user, ChatMessageRepository $chatRepo): Response
    {
        $messages = $chatRepo->findPrivateMessages($this->getUser(), $user);
        return $this->render('chat/private.html.twig', [
            'otherUser' => $user,
            'messages' => array_reverse($messages),
        ]);
    }

    #[Route('/messages/{id}/send', name: 'app_private_chat_send', methods: ['POST'])]
    public function sendPrivateMessage(User $recipient, Request $request, EntityManagerInterface $em, NotificationService $notifService): Response
    {
        $content = trim($request->request->get('message', ''));
        if (empty($content)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'error'], 400);
            }
            return $this->redirectToRoute('app_private_chat', ['id' => $recipient->getId()]);
        }

        $message = new ChatMessage();
        $message->setSender($this->getUser());
        $message->setRecipient($recipient);
        $message->setContent($content);
        $message->setIsPrivate(true);

        $em->persist($message);
        $em->flush();

        // Notify recipient about new private message
        $sender = $this->getUser();
        $notifService->create(
            $recipient,
            'system',
            $sender->getUsername() . ' надіслав вам повідомлення',
            '/messages/' . $sender->getId()
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => 'ok',
                'message' => $this->formatMessage($message),
            ]);
        }

        return $this->redirectToRoute('app_private_chat', ['id' => $recipient->getId()]);
    }

    #[Route('/messages/{id}/poll', name: 'app_private_chat_poll')]
    public function privatePoll(User $user, Request $request, ChatMessageRepository $chatRepo): JsonResponse
    {
        $afterId = (int) $request->query->get('after', 0);
        $currentUser = $this->getUser();

        $qb = $chatRepo->createQueryBuilder('m')
            ->where('m.isPrivate = true')
            ->andWhere(
                '(m.sender = :u1 AND m.recipient = :u2) OR (m.sender = :u2 AND m.recipient = :u1)'
            )
            ->setParameter('u1', $currentUser)
            ->setParameter('u2', $user)
            ->orderBy('m.createdAt', 'ASC');

        if ($afterId > 0) {
            $qb->andWhere('m.id > :afterId')->setParameter('afterId', $afterId);
        }

        $messages = $qb->getQuery()->getResult();

        return new JsonResponse([
            'messages' => array_map(fn($m) => $this->formatMessage($m), $messages),
        ]);
    }

    #[Route('/events/{id}/chat', name: 'app_event_chat')]
    public function eventChat(GameEvent $event, ChatMessageRepository $chatRepo): Response
    {
        $messages = $chatRepo->createQueryBuilder('m')
            ->where('m.events = :event')
            ->setParameter('event', $event)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()->getResult();

        return $this->render('chat/events.html.twig', [
            'events' => $event,
            'messages' => array_reverse($messages),
        ]);
    }

    #[Route('/events/{id}/chat/send', name: 'app_event_chat_send', methods: ['POST'])]
    public function sendEventMessage(GameEvent $event, Request $request, EntityManagerInterface $em): Response
    {
        $content = trim($request->request->get('message', ''));
        if (empty($content)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'error'], 400);
            }
            return $this->redirectToRoute('app_event_chat', ['id' => $event->getId()]);
        }

        $message = new ChatMessage();
        $message->setSender($this->getUser());
        $message->setEvent($event);
        $message->setContent($content);

        $em->persist($message);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'ok', 'message' => $this->formatMessage($message)]);
        }

        return $this->redirectToRoute('app_event_chat', ['id' => $event->getId()]);
    }

    #[Route('/events/{id}/chat/poll', name: 'app_event_chat_poll')]
    public function eventPoll(GameEvent $event, Request $request, ChatMessageRepository $chatRepo): JsonResponse
    {
        $afterId = (int) $request->query->get('after', 0);

        $qb = $chatRepo->createQueryBuilder('m')
            ->where('m.events = :event')
            ->setParameter('event', $event)
            ->orderBy('m.createdAt', 'ASC');

        if ($afterId > 0) {
            $qb->andWhere('m.id > :afterId')->setParameter('afterId', $afterId);
        }

        $messages = $qb->getQuery()->getResult();

        return new JsonResponse([
            'messages' => array_map(fn($m) => $this->formatMessage($m), $messages),
        ]);
    }

    private function formatMessage(ChatMessage $m): array
    {
        return [
            'id' => $m->getId(),
            'content' => htmlspecialchars($m->getContent()),
            'senderId' => $m->getSender()->getId(),
            'senderName' => $m->getSender()->getUsername(),
            'senderAvatar' => $m->getSender()->getAvatar(),
            'type' => $m->getType(),
            'attachmentUrl' => $m->getAttachmentUrl(),
            'createdAt' => $m->getCreatedAt()->format('H:i'),
        ];
    }

    private function isImage(?string $ext): bool
    {
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
