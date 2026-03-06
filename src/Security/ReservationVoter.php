<?php

namespace App\Security;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ReservationVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Si le sujet n'est pas une Reservation, ce voter ne s'applique pas
        if (!$subject instanceof Reservation) {
            return false;
        }

        // Ce voter gère les attributs EDIT, DELETE, VIEW
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas connecté, refuser l'accès
        if (!$user instanceof User) {
            return false;
        }

        /** @var Reservation $reservation */
        $reservation = $subject;

        // Les admins ont toujours accès
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Pour VIEW, EDIT, DELETE : vérifier que c'est la réservation de l'utilisateur
        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                return $reservation->getClient() === $user;
        }

        return false;
    }
}

