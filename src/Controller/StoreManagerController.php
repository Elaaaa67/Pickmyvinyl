<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\StockRepository;
use App\Repository\StoreRepository;
use App\Repository\VinylRepository;
use App\Entity\Vinyl;
use App\Entity\Stock;
use Calliostro\Discogs\DiscogsClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/store-manager')]
class StoreManagerController extends AbstractController
{
    #[Route('/dashboard', name: 'app_store_manager_dashboard', methods: ['GET'])]
    public function dashboard(
        StoreRepository $storeRepository,
        StockRepository $stockRepository,
        ReservationRepository $reservationRepository
    ): Response
    {
        // Vérifier que l'utilisateur a le rôle ROLE_STORE
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();

        // Récupérer le magasin associé à cet utilisateur
        $store = $storeRepository->findOneBy(['owner' => $user]);

        if (!$store) {
            throw $this->createNotFoundException('Aucun magasin associé à votre compte.');
        }

        // Récupérer les stocks de ce magasin
        $stocks = $stockRepository->findBy(['store' => $store]);

        // Récupérer les réservations pour ce magasin
        $reservations = $reservationRepository->findBy(['store' => $store], ['createdAt' => 'DESC']);

        // Statistiques
        $stats = [
            'totalVinyls' => count($stocks),
            'totalReservations' => count($reservations),
            'pendingReservations' => count(array_filter($reservations, fn($r) => $r->getStatus() === 'En attente')),
            'confirmedReservations' => count(array_filter($reservations, fn($r) => $r->getStatus() === 'Confirmée')),
            'totalStock' => array_sum(array_map(fn($s) => $s->getQuantity(), $stocks)),
        ];

        return $this->render('store_manager/dashboard.html.twig', [
            'store' => $store,
            'stocks' => $stocks,
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }

    #[Route('/stocks', name: 'app_store_manager_stocks', methods: ['GET'])]
    public function stocks(
        Request $request,
        StockRepository $stockRepository,
        DiscogsClient $discogsClient
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();
        $store = $user->getStore();

        if (!$store) {
            throw $this->createNotFoundException('Aucun magasin associé à votre compte.');
        }

        // Afficher UNIQUEMENT les stocks de ce magasin
        $stocks = $stockRepository->findBy(['store' => $store]);

        // Recherche Discogs si query présente
        $discogsResults = [];
        $query = $request->query->get('q');

        if ($query) {
            try {
                $response = $discogsClient->search($query, ['type' => 'release']);
                $discogsResults = $response['results'] ?? [];

                // Limiter à 10 résultats
                $discogsResults = array_slice($discogsResults, 0, 10);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la recherche Discogs : ' . $e->getMessage());
            }
        }

        return $this->render('store_manager/stocks.html.twig', [
            'store' => $store,
            'stocks' => $stocks,
            'discogsResults' => $discogsResults,
        ]);
    }

    #[Route('/import-discogs/{storeId}', name: 'app_store_manager_import_discogs', methods: ['POST'])]
    public function importDiscogs(
        int $storeId,
        Request $request,
        DiscogsClient $discogsClient,
        VinylRepository $vinylRepository,
        StockRepository $stockRepository,
        StoreRepository $storeRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();
        $store = $user->getStore();

        if (!$store || $store->getId() !== $storeId) {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        $discogsId = $request->request->get('discogsId');
        $quantity = (int) $request->request->get('quantity', 1);

        if (!$discogsId || $quantity < 1) {
            $this->addFlash('error', 'Données invalides.');
            return $this->redirectToRoute('app_store_manager_stocks', ['id' => $storeId]);
        }

        try {
            // Récupérer les infos complètes depuis Discogs
            $release = $discogsClient->getRelease((int) $discogsId);

            // Vérifier si le vinyle existe déjà dans notre BD
            $vinyl = $vinylRepository->findOneBy(['discogsId' => $discogsId]);

            if (!$vinyl) {
                // Créer le vinyle
                $vinyl = new Vinyl();
                $vinyl->setTitle($release['title'] ?? 'Titre inconnu');
                $vinyl->setArtist($release['artists'][0]['name'] ?? 'Artiste inconnu');
                $vinyl->setDiscogsId((string) $discogsId);
                $vinyl->setCoverImage($release['thumb'] ?? $release['images'][0]['uri'] ?? '');
                $vinyl->setYear($release['year'] ?? null);

                $entityManager->persist($vinyl);
                $entityManager->flush();
            }

            // Vérifier si un stock existe déjà pour ce vinyle dans ce magasin
            $stock = $stockRepository->findOneBy(['vinyl' => $vinyl, 'store' => $store]);

            if ($stock) {
                // Augmenter la quantité
                $stock->setQuantity($stock->getQuantity() + $quantity);
            } else {
                // Créer un nouveau stock
                $stock = new Stock();
                $stock->setVinyl($vinyl);
                $stock->setStore($store);
                $stock->setQuantity($quantity);
                $entityManager->persist($stock);
            }

            $entityManager->flush();

            $this->addFlash('success', sprintf('"%s" a été ajouté à votre stock (%d exemplaires).', $vinyl->getTitle(), $quantity));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_store_manager_stocks', ['id' => $storeId]);
    }

    #[Route('/reservations', name: 'app_store_manager_reservations', methods: ['GET'])]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();
        $store = $user->getStore();

        if (!$store) {
            throw $this->createNotFoundException('Aucun magasin associé à votre compte.');
        }

        // Afficher UNIQUEMENT les réservations pour ce magasin
        $reservations = $reservationRepository->findBy(['store' => $store], ['createdAt' => 'DESC']);

        return $this->render('store_manager/reservations.html.twig', [
            'store' => $store,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/pending-reservations', name: 'app_store_manager_pending_reservations', methods: ['GET'])]
    public function pendingReservations(
        StoreRepository $storeRepository,
        ReservationRepository $reservationRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();
        $store = $storeRepository->findOneBy(['owner' => $user]);

        if (!$store) {
            throw $this->createNotFoundException('Aucun magasin associé à votre compte.');
        }

        // Récupérer les réservations en attente (pending) pour ce magasin
        $pendingReservations = $reservationRepository->findBy(
            ['store' => $store, 'status' => 'pending'],
            ['createdAt' => 'DESC']
        );

        return $this->render('store_manager/pending_reservations.html.twig', [
            'store' => $store,
            'pendingReservations' => $pendingReservations,
        ]);
    }

    #[Route('/validate-reservation/{id}', name: 'app_store_manager_validate_reservation', methods: ['POST'])]
    public function validateReservation(
        int $id,
        ReservationRepository $reservationRepository,
        StoreRepository $storeRepository,
        \App\Service\ReservationService $reservationService
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();
        $store = $storeRepository->findOneBy(['owner' => $user]);

        if (!$store) {
            throw $this->createNotFoundException('Aucun magasin associé à votre compte.');
        }

        $reservation = $reservationRepository->find($id);

        if (!$reservation || $reservation->getStore() !== $store) {
            throw $this->createAccessDeniedException('Accès refusé à cette réservation.');
        }

        try {
            $reservationService->validateReservation($reservation);
            $this->addFlash('success', 'Réservation validée avec succès!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_store_manager_pending_reservations');
    }

    #[Route('/reject-reservation/{id}', name: 'app_store_manager_reject_reservation', methods: ['POST'])]
    public function rejectReservation(
        int $id,
        ReservationRepository $reservationRepository,
        StoreRepository $storeRepository,
        \App\Service\ReservationService $reservationService
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STORE');

        $user = $this->getUser();
        $store = $storeRepository->findOneBy(['owner' => $user]);

        if (!$store) {
            throw $this->createNotFoundException('Aucun magasin associé à votre compte.');
        }

        $reservation = $reservationRepository->find($id);

        if (!$reservation || $reservation->getStore() !== $store) {
            throw $this->createAccessDeniedException('Accès refusé à cette réservation.');
        }

        try {
            $reservationService->rejectReservation($reservation);
            $this->addFlash('success', 'Réservation rejetée.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_store_manager_pending_reservations');
    }
}
