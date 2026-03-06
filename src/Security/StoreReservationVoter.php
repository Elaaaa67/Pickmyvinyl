<?php

namespace App\Security;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class StoreReservationVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT_STATUS = 'EDIT_STATUS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Reservation) {
            return false;
        }

        return in_array($attribute, [self::VIEW, self::EDIT_STATUS]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Reservation $reservation */
        $reservation = $subject;

        // Les admins ont toujours accès
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // ROLE_STORE peut voir et modifier le statut des réservations DE SON MAGASIN
        if (in_array('ROLE_STORE', $user->getRoles())) {
            // Vérifier que la réservation est pour ce magasin
            if ($reservation->getStore() === $user->getStore()) {
                return true;
            }
        }

        return false;
    }
}

