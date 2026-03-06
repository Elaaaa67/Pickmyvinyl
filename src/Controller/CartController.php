<?php

namespace App\Controller;

use App\Repository\VinylRepository;
use App\Repository\StockRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_USER')] // Oblige l'utilisateur à être connecté
class CartController extends AbstractController
{
    /**
     * Affiche le contenu du panier avec les détails des vinyles et stocks
     */
    #[Route('/', name: 'app_cart_show', methods: ['GET'])]
    public function show(CartService $cartService): Response
    {
        $cart = $cartService->getCart();
        $cartWithDetails = [];

        // Les données du vinyle sont déjà en session
        foreach ($cart['items'] as $vinylId => $item) {
            $cartWithDetails[] = [
                'vinylId' => $vinylId,
                'title' => $item['title'] ?? 'Vinyle inconnu',
                'artist' => $item['artist'] ?? 'Artiste inconnu',
                'coverImage' => $item['coverImage'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
            ];
        }

        // Résumé du panier
        $summary = $cartService->getSummary();

        return $this->render('cart/show.html.twig', [
            'cartItems' => $cartWithDetails,
            'cartSummary' => [
                'totalQuantity' => $summary['itemsCount'] ?? 0,
                'itemsDetails' => $summary['itemsDetails'] ?? []
            ]
        ]);
    }

    /**
     * Ajoute un vinyle au panier via POST
     */
    #[Route('/add', name: 'app_cart_add', methods: ['POST'])]
    public function add(Request $request, CartService $cartService): Response
    {
        $vinylId = (int) $request->request->get('vinylId');
        $quantity = (int) ($request->request->get('quantity') ?? 1);
        $title = $request->request->get('title') ?? 'Vinyle #' . $vinylId;
        $artist = $request->request->get('artist') ?? 'Artiste inconnu';
        $coverImage = $request->request->get('coverImage') ?? '';

        // Ajouter l'item au panier avec les données complètes
        $cartService->addItem($vinylId, $quantity, [
            'title' => $title,
            'artist' => $artist,
            'coverImage' => $coverImage,
        ]);

        $this->addFlash('success', $title . ' a été ajouté à votre panier.');

        // Redirection vers le panier
        return $this->redirectToRoute('app_cart_show');
    }

    /**
     * Supprime un article du panier
     */
    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST', 'GET'])]
    public function remove(int $id, CartService $cartService): Response
    {
        $cartService->removeItem($id);
        $this->addFlash('success', 'Article retiré du panier.');

        return $this->redirectToRoute('app_cart_show');
    }

    /**
     * Vide tout le panier
     */
    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(CartService $cartService): Response
    {
        $cartService->clearCart();
        $this->addFlash('success', 'Votre panier a été vidé.');

        return $this->redirectToRoute('app_cart_show');
    }

    /**
     * Met à jour la quantité d'un item dans le panier
     */
    #[Route('/update', name: 'app_cart_update', methods: ['POST'])]
    public function update(Request $request, CartService $cartService): Response
    {
        $vinylId = (int) $request->request->get('vinylId');
        $quantity = (int) $request->request->get('quantity', 1);

        $cartService->updateItem($vinylId, $quantity);
        $this->addFlash('success', 'Quantité mise à jour.');

        return $this->redirectToRoute('app_cart_show');
    }
}
