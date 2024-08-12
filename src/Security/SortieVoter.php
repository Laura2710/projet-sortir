<?php

namespace App\Security;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortieVoter extends Voter
{
    const VIEW = 'view';
    const CANCEL = 'cancel';
    const SUBSCRIBE = 'subscribe';
    const UNSUBSCRIBE = 'unsubscribe';
    const MANAGE = 'manage';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [
            self::VIEW,
            self::MANAGE,
            self::CANCEL,
            self::SUBSCRIBE,
            self::UNSUBSCRIBE])) {
            return false;
        }
        if (!$subject instanceof Sortie) {
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Participant) {
            return false;
        }

        $sortie = $subject;
        return match ($attribute) {
            self::VIEW => $this->canView($sortie),
            self::MANAGE => $this->canManage($sortie, $user),
            self::SUBSCRIBE => $this->canSubscribe($sortie, $user),
            self::UNSUBSCRIBE => $this->canUnsubscribe($sortie, $user),
            self::CANCEL => $this->canCancel($sortie, $user),
            default => false,
        };

    }

    private function canView(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle()->value != 'Créée' && $sortie->getEtat()->getLibelle()->value != 'Activité passée') {
            return true;
        }
        return false;
    }

    private function canManage(Sortie $sortie, Participant $user)
    {
        if ($sortie->getOrganisateur() == $user && $sortie->getEtat()->getLibelle()->value == 'Créée') {
            return true;
        }
        return false;
    }

    private function canSubscribe(Sortie $sortie, Participant $user)
    {
        if ($sortie->getOrganisateur() != $user && $sortie->getEtat()->getLibelle()->value == 'Ouverte' && !$sortie->getParticipants()->contains($user)) {
            return true;
        }
        return false;
    }

    private function canUnsubscribe(mixed $sortie, Participant $user)
    {
        if ($sortie->getOrganisateur() != $user
            && ($sortie->getEtat()->getLibelle()->value == 'Ouverte' || $sortie->getEtat()->getLibelle()->value == 'Cloturee')
            && $sortie->getParticipants()->contains($user)) {
            return true;
        }
        return false;
    }

    private function canCancel(Sortie $sortie, Participant $user)
    {
        if (($sortie->getOrganisateur() == $user || $user->isAdministrateur()) && $sortie->getEtat()->getLibelle()->value == 'Ouverte') {
            return true;
        }
        return false;
    }


}