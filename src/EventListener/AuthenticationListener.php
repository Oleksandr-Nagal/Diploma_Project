<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class AuthenticationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'onCheckPassport'];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof User) {
            return;
        }

        if ($user->isBanned()) {
            throw new CustomUserMessageAuthenticationException(self::buildBanMessage($user));
        }
    }

    public static function buildBanMessage(User $user): string
    {
        if ($user->getBannedUntil() && $user->getBannedUntil() > new \DateTime()) {
            $left = $user->getBanTimeLeft();
            return sprintf(
                'Ваш акаунт заблоковано до %s (залишилось %s).',
                $user->getBannedUntil()->format('d.m.Y H:i'),
                $left ?? '—'
            );
        }

        return 'Ваш акаунт заблоковано адміністратором без обмеження в часі.';
    }
}
