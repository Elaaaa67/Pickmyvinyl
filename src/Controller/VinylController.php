<?php

namespace App\Controller;

use App\Entity\Vinyl;
use App\Form\VinylType;
use App\Repository\VinylRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Calliostro\Discogs\DiscogsClient;

#[Route('/vinyl')]
class VinylController extends AbstractController
{
    #[Route('/', name: 'app_vinyl_index', methods: ['GET'])]
    public function index(Request $request, VinylRepository $vinylRepository, DiscogsClient $client): Response
    {
        $q = trim((string) $request->query->get('q', ''));

        // Si une query est fournie, faire une recherche Discogs (UX)
        $external = [];
        if (strlen($q) >= 3) {
            try {
                $results = $client->search(q: $q, type: 'release', format: 'vinyl', perPage: 12);
                $external = $results['results'] ?? [];
            } catch (\Throwable $e) {
                // ignorer l'erreur externe
                $external = [];
            }
        }

        // Récupérer uniquement les vinyles disponibles en stock
        $vinyls = $vinylRepository->findAvailable();

        return $this->render('vinyl/index.html.twig', [
            'vinyls' => $vinyls,
            'external' => $external,
            'query' => $q,
        ]);
    }

    #[Route('/new', name: 'app_vinyl_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_SHOP') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $vinyl = new Vinyl();
        $form = $this->createForm(VinylType::class, $vinyl);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($vinyl);
            $em->flush();

            return $this->redirectToRoute('app_vinyl_show', ['id' => $vinyl->getId()]);
        }

        return $this->render('vinyl/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_vinyl_show', methods: ['GET'])]
    public function show(Vinyl $vinyl): Response
    {
        $stocks = $vinyl->getStocks();

        return $this->render('vinyl/show.html.twig', [
            'vinyl' => $vinyl,
            'stocks' => $stocks,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vinyl_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vinyl $vinyl, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_SHOP') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(VinylType::class, $vinyl);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_vinyl_show', ['id' => $vinyl->getId()]);
        }

        return $this->render('vinyl/form.html.twig', [
            'form' => $form->createView(),
            'vinyl' => $vinyl,
        ]);
    }

    #[Route('/{id}', name: 'app_vinyl_delete', methods: ['POST'])]
    public function delete(Request $request, Vinyl $vinyl, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete' . $vinyl->getId(), $request->request->get('_token'))) {
            $em->remove($vinyl);
            $em->flush();
        }

        return $this->redirectToRoute('app_vinyl_index');
    }

    #[Route('/store/index', name: 'app_vinyl_store_index', methods: ['GET'])]
    public function storeIndex(VinylRepository $vinylRepository): Response
    {
        // Protège l'accès aux magasins seulement
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();
        $store = $user->getStore();

        if (!$store) {
            $this->addFlash('error', 'Vous n\'avez pas d\'enseigne associée.');
            return $this->redirectToRoute('app_home');
        }

        // Récupère les vinyles disponibles dans ce magasin (via les stocks)
        $vinyls = $vinylRepository->findByStore($store);

        return $this->render('vinyl/store_index.html.twig', [
            'vinyls' => $vinyls,
            'store' => $store,
        ]);
    }

    #[Route('/panier', name: 'app_vinyl_panier', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function panier(CartService $cartService): Response
    {
        // Récupérer le panier de la session
        $cart = $cartService->getCart();
        $cartItems = [];

        // Les données sont déjà en session, pas besoin de chercher en BDD
        foreach ($cart['items'] as $vinylId => $item) {
            $cartItems[] = [
                'vinylId' => $vinylId,
                'title' => $item['title'] ?? 'Vinyle inconnu',
                'artist' => $item['artist'] ?? 'Artiste inconnu',
                'coverImage' => $item['coverImage'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
            ];
        }

        return $this->render('vinyl/panier.html.twig', [
            'cartItems' => $cartItems,
            'cartTotal' => count($cartItems),
        ]);
    }
}
