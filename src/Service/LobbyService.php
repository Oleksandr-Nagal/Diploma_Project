<?php

namespace App\Service;

use App\Entity\Lobby;
use App\Entity\LobbyMember;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class LobbyService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationService $notificationService
    ) {}

    public function createLobby(User $owner, Lobby $lobby): Lobby
    {
        $lobby->setOwner($owner);
        $lobby->setStatus('open');

        $member = new LobbyMember();
        $member->setLobby($lobby);
        $member->setUser($owner);
        $member->setRole('owner');
        $member->setStatus('accepted');

        $this->em->persist($lobby);
        $this->em->persist($member);
        $this->em->flush();

        return $lobby;
    }

    public function joinLobby(User $user, Lobby $lobby): ?LobbyMember
    {
        if ($lobby->isFull() || $lobby->getStatus() !== 'open') {
            return null;
        }

        foreach ($lobby->getMembers() as $m) {
            if ($m->getUser() === $user) {
                return null;
            }
        }

        $member = new LobbyMember();
        $member->setLobby($lobby);
        $member->setUser($user);
        $member->setStatus($lobby->isPrivate() ? 'pending' : 'accepted');

        $this->em->persist($member);
        $this->em->flush();

        if ($lobby->isPrivate()) {
            $this->notificationService->create(
                $lobby->getOwner(),
                'lobby_invite',
                $user->getUsername() . ' хоче приєднатися до "' . $lobby->getTitle() . '"',
                '/lobby/' . $lobby->getId()
            );
        }

        if ($lobby->isFull()) {
            $lobby->setStatus('full');
            $this->em->flush();
        }

        return $member;
    }

    public function leaveLobby(User $user, Lobby $lobby): void
    {
        foreach ($lobby->getMembers() as $member) {
            if ($member->getUser() === $user) {
                $this->em->remove($member);
                break;
            }
        }

        if ($lobby->getOwner() === $user) {
            $lobby->setStatus('closed');
        } elseif ($lobby->getStatus() === 'full') {
            $lobby->setStatus('open');
        }

        $this->em->flush();
    }

    public function acceptMember(LobbyMember $member): void
    {
        $member->setStatus('accepted');
        $this->em->flush();

        $this->notificationService->create(
            $member->getUser(),
            'lobby_invite',
            'Вас прийнято до лобі "' . $member->getLobby()->getTitle() . '"',
            '/lobby/' . $member->getLobby()->getId()
        );
    }

    public function rejectMember(LobbyMember $member): void
    {
        $member->setStatus('rejected');
        $this->em->flush();
    }
}
