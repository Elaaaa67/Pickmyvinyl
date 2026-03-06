<?php

namespace App\Security;

use App\Entity\Store;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class StoreVoter extends Voter
{
    public const MANAGE = 'MANAGE';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Store) {
            return false;
        }

        return in_array($attribute, [self::MANAGE, self::VIEW]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Store $store */
        $store = $subject;

        // Les admins ont toujours accès
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Les ROLE_STORE peuvent UNIQUEMENT gérer leur propre magasin
        if (in_array('ROLE_STORE', $user->getRoles())) {
            return $store === $user->getStore();
        }

        // Les utilisateurs normaux ne peuvent pas gérer les magasins
        return false;
    }
}

