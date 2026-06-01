<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Entity\GameEvent;
use App\Entity\Lobby;
use App\Entity\User;
use App\Repository\ChatMessageRepository;
use App\Service\CloudinaryService;
use App\Service\EmojiService;
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
        $user = $this->getUser();
        return $this->render('chat/lobby.html.twig', [
            'lobby' => $lobby,
            'messages' => array_reverse($messages),
            'emojis' => EmojiService::getEmojis($user->isPremium()),
            'allStickers' => EmojiService::PREMIUM_STICKERS,
        ]);
    }

    #[Route('/lobby/{id}/chat/send', name: 'app_lobby_chat_send', methods: ['POST'])]
    public function sendLobbyMessage(Lobby $lobby, Request $request, EntityManagerInterface $em, CloudinaryService $cloudinary): Response
    {
        $content = trim($request->request->get('message', ''));

        if (preg_match('/\[sticker:([a-z_]+)\]/', $content, $matches)) {
            if (!$this->getUser()->isPremium() || !EmojiService::isValidSticker($matches[1])) {
                $content = '';
            }
        }

        $voiceFile = $request->files->get('voice');
        $attachment = $request->files->get('attachment');
        if (empty($content) && !$attachment && !$voiceFile) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'error', 'message' => 'Empty'], 400);
            }
            return $this->redirectToRoute('app_lobby_chat', ['id' => $lobby->getId()]);
        }

        $message = new ChatMessage();
        $message->setSender($this->getUser());
        $message->setLobby($lobby);
        $message->setContent($content ?: '');

        if ($voiceFile && $voiceFile->isValid()) {
            $voiceUrl = $this->storeVoice($voiceFile, $cloudinary);
            if ($voiceUrl) {
                $message->setType('voice');
                $message->setAttachmentUrl($voiceUrl);
                $message->setContent('[Голосове повідомлення]');
            }
        }

        if ($attachment && $attachment->isValid()) {
            $error = $this->validateAttachment($attachment);
            if ($error) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['status' => 'error', 'message' => $error], 400);
                }
                return $this->redirect($request->headers->get('referer') ?? '/');
            }
            $this->applyAttachment($message, $attachment, $cloudinary, $content);
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
        $currentUser = $this->getUser();
        $messages = $chatRepo->findPrivateMessages($currentUser, $user);
        return $this->render('chat/private.html.twig', [
            'otherUser' => $user,
            'messages' => array_reverse($messages),
            'emojis' => EmojiService::getEmojis($currentUser->isPremium()),
            'allStickers' => EmojiService::PREMIUM_STICKERS,
        ]);
    }

    #[Route('/messages/{id}/send', name: 'app_private_chat_send', methods: ['POST'])]
    public function sendPrivateMessage(User $recipient, Request $request, EntityManagerInterface $em, NotificationService $notifService, CloudinaryService $cloudinary): Response
    {
        $content = trim($request->request->get('message', ''));
        $voiceFile = $request->files->get('voice');
        $attachment = $request->files->get('attachment');

        if (empty($content) && !$voiceFile && !$attachment) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'error'], 400);
            }
            return $this->redirectToRoute('app_private_chat', ['id' => $recipient->getId()]);
        }

        $message = new ChatMessage();
        $message->setSender($this->getUser());
        $message->setRecipient($recipient);
        $message->setIsPrivate(true);
        $message->setContent($content);

        if ($voiceFile && $voiceFile->isValid()) {
            $voiceUrl = $this->storeVoice($voiceFile, $cloudinary);
            if (!$voiceUrl) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Upload failed'], 500);
                }
                return $this->redirectToRoute('app_private_chat', ['id' => $recipient->getId()]);
            }
            $message->setType('voice');
            $message->setAttachmentUrl($voiceUrl);
            $message->setContent('[Голосове повідомлення]');
        }

        if ($attachment && $attachment->isValid()) {
            $error = $this->validateAttachment($attachment);
            if ($error) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['status' => 'error', 'message' => $error], 400);
                }
                return $this->redirect($request->headers->get('referer') ?? '/');
            }
            $this->applyAttachment($message, $attachment, $cloudinary, $content);
        }

        $em->persist($message);
        $em->flush();

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
            'allStickers' => EmojiService::PREMIUM_STICKERS,
        ]);
    }

    #[Route('/events/{id}/chat/send', name: 'app_event_chat_send', methods: ['POST'])]
    public function sendEventMessage(GameEvent $event, Request $request, EntityManagerInterface $em, CloudinaryService $cloudinary): Response
    {
        $content = trim($request->request->get('message', ''));
        $voiceFile = $request->files->get('voice');
        $attachment = $request->files->get('attachment');

        if (empty($content) && !$voiceFile && !$attachment) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['status' => 'error'], 400);
            }
            return $this->redirectToRoute('app_event_chat', ['id' => $event->getId()]);
        }

        $message = new ChatMessage();
        $message->setSender($this->getUser());
        $message->setEvent($event);
        $message->setContent($content);

        if ($voiceFile && $voiceFile->isValid()) {
            $voiceUrl = $this->storeVoice($voiceFile, $cloudinary);
            if (!$voiceUrl) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Upload failed'], 500);
                }
                return $this->redirectToRoute('app_event_chat', ['id' => $event->getId()]);
            }
            $message->setType('voice');
            $message->setAttachmentUrl($voiceUrl);
            $message->setContent('[Голосове повідомлення]');
        }

        if ($attachment && $attachment->isValid()) {
            $error = $this->validateAttachment($attachment);
            if ($error) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['status' => 'error', 'message' => $error], 400);
                }
                return $this->redirect($request->headers->get('referer') ?? '/');
            }
            $this->applyAttachment($message, $attachment, $cloudinary, $content);
        }

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
            'createdAt' => $m->getCreatedAt()->format('c'),
        ];
    }

    private function isImage(?string $ext): bool
    {
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    private const MAX_ATTACHMENT_BYTES = 5 * 1024 * 1024;
    private const ALLOWED_ATTACHMENT_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'doc', 'docx'];

    private function resolveExtension(\Symfony\Component\HttpFoundation\File\UploadedFile $file): string
    {
        $clientExt = strtolower($file->getClientOriginalExtension());
        if (in_array($clientExt, self::ALLOWED_ATTACHMENT_EXT, true)) {
            return $clientExt;
        }
        return strtolower($file->guessExtension() ?? '');
    }

    private function validateAttachment(\Symfony\Component\HttpFoundation\File\UploadedFile $file): ?string
    {
        if ($file->getSize() > self::MAX_ATTACHMENT_BYTES) {
            return 'Файл занадто великий. Максимум 5 МБ.';
        }
        $ext = $this->resolveExtension($file);
        if (!in_array($ext, self::ALLOWED_ATTACHMENT_EXT, true)) {
            return 'Дозволено лише зображення (JPG, PNG, GIF, WEBP) або документи (DOC, DOCX).';
        }
        return null;
    }

    private function applyAttachment(ChatMessage $message, \Symfony\Component\HttpFoundation\File\UploadedFile $file, CloudinaryService $cloudinary, string $content): void
    {
        $ext = $this->resolveExtension($file) ?: 'bin';
        $url = $cloudinary->isConfigured() ? $cloudinary->upload($file, 'gamefinder/chat') : null;

        if (!$url) {
            $filename = uniqid() . '.' . $ext;
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/chat';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $file->move($uploadDir, $filename);
            $url = '/uploads/chat/' . $filename;
        }

        $isImage = $this->isImage($ext);
        $message->setAttachmentUrl($url);
        $message->setType($isImage ? 'image' : 'file');
        if (empty($content)) {
            $message->setContent($isImage ? '[Зображення]' : '[Файл]');
        }
    }

    private function storeVoice(\Symfony\Component\HttpFoundation\File\UploadedFile $file, CloudinaryService $cloudinary): ?string
    {
        if ($file->getSize() > 5 * 1024 * 1024) {
            return null;
        }

        if ($cloudinary->isConfigured()) {
            $url = $cloudinary->upload($file, 'gamefinder/voice');
            if ($url) {
                return $url;
            }
        }

        $ext = $file->guessExtension() ?: 'webm';
        $filename = uniqid('voice_') . '.' . $ext;
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/voice';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        try {
            $file->move($uploadDir, $filename);
        } catch (\Exception $e) {
            return null;
        }

        return '/uploads/voice/' . $filename;
    }
}
