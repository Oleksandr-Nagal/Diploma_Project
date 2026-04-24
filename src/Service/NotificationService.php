<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function create(User $user, string $type, string $message, ?string $link = null): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setLink($link);

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    public function notifyLobbyInvite(User $user, string $lobbyTitle, int $lobbyId): void
    {
        $this->create($user, 'lobby_invite', "Вас запрошено до лобі \"$lobbyTitle\"", "/lobby/$lobbyId");
    }

    public function notifyFriendRequest(User $user, string $fromUsername): void
    {
        $this->create($user, 'friend_request', "$fromUsername хоче додати вас у друзі", '/friends');
    }

    public function notifyGameStart(User $user, string $lobbyTitle, int $lobbyId): void
    {
        $this->create($user, 'game_start', "Гра в лобі \"$lobbyTitle\" починається!", "/lobby/$lobbyId");
    }

    public function notifyNewReview(User $user, string $fromUsername, bool $isPositive): void
    {
        $type = $isPositive ? 'позитивний' : 'негативний';
        $this->create($user, 'review', "$fromUsername залишив $type відгук", '/profile');
    }

    public function notifyEventReminder(User $user, string $eventTitle, int $eventId): void
    {
        $this->create($user, 'system', "Нагадування: подія \"$eventTitle\" скоро почнеться", "/events/$eventId");
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->em->flush();
    }

    public function markAllAsRead(User $user): void
    {
        $this->em->createQueryBuilder()
            ->update(Notification::class, 'n')
            ->set('n.isRead', 'true')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
