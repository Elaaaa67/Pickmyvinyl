<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'shopping_cart';

    public function __construct(private RequestStack $requestStack)
    {
    }

    private function getSession()
    {
        $session = $this->requestStack->getSession();

        // Démarrer la session si elle n'est pas déjà active
        if (!$session->isStarted()) {
            $session->start();
        }

        return $session;
    }

    /**
     * Ajoute un vinyle au panier
     */
    public function addItem(int $vinylId, int $quantity = 1, array $vinylData = []): array
    {
        $session = $this->getSession();
        $cart = $this->getCart();

        if (isset($cart['items'][$vinylId])) {
            $cart['items'][$vinylId]['quantity'] += $quantity;
        } else {
            $cart['items'][$vinylId] = [
                'vinylId' => $vinylId,
                'quantity' => $quantity,
                'title' => $vinylData['title'] ?? 'Vinyle #' . $vinylId,
                'artist' => $vinylData['artist'] ?? 'Artiste inconnu',
                'coverImage' => $vinylData['coverImage'] ?? null,
            ];
        }

        // On enregistre
        $session->set(self::CART_SESSION_KEY, $cart);

        // TRÈS IMPORTANT : On force la sauvegarde immédiate en session
        $session->save();

        return $cart;
    }

    /**
     * Met à jour la quantité d'un vinyle
     */
    public function updateItem(int $vinylId, int $quantity): array
    {
        $cart = $this->getCart();

        if ($quantity <= 0) {
            // Si quantité <= 0, supprimer l'item
            unset($cart['items'][$vinylId]);
        } else {
            if (isset($cart['items'][$vinylId])) {
                $cart['items'][$vinylId]['quantity'] = $quantity;
            }
        }

        $this->getSession()->set(self::CART_SESSION_KEY, $cart);
        return $cart;
    }

    /**
     * Supprime un vinyle du panier
     */
    public function removeItem(int $vinylId): array
    {
        $cart = $this->getCart();
        unset($cart['items'][$vinylId]);
        $this->getSession()->set(self::CART_SESSION_KEY, $cart);
        return $cart;
    }

    /**
     * Retourne le panier complet
     */
    public function getCart(): array
    {
        $cart = $this->getSession()->get(self::CART_SESSION_KEY);

        // Initialiser le panier s'il n'existe pas
        if (!is_array($cart)) {
            $cart = ['items' => []];
        }

        return $cart;
    }

    /**
     * Vide le panier
     */
    public function clearCart(): void
    {
        $this->getSession()->set(self::CART_SESSION_KEY, ['items' => []]);
    }

    /**
     * Retourne un résumé du panier
     */
    public function getSummary(): array
    {
        $cart = $this->getCart();
        $itemCount = 0;

        foreach ($cart['items'] as $item) {
            $itemCount += $item['quantity'];
        }

        return [
            'itemsCount' => $itemCount,
            'itemsDetails' => $cart['items'],
        ];
    }
}

