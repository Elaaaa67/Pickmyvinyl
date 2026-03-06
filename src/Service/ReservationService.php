<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Vinyl;
use App\Entity\Store;
use App\Repository\StockRepository;
use App\Repository\VinylRepository;
use App\Repository\StoreRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StockRepository $stockRepository,
        private VinylRepository $vinylRepository,
        private StoreRepository $storeRepository
    ) {
    }

    /**
     * Crée une réservation à partir d'un ID Discogs (depuis le panier)
     * Si le vinyle n'existe pas en BDD, il est créé automatiquement
     */
    public function createReservationFromDiscogs(
        User $user,
        int $discogsVinylId,
        int $storeId,
        int $quantity = 1
    ): Reservation {
        if ($quantity <= 0) {
            throw new \Exception('La quantité doit être supérieure à 0.');
        }

        // Récupérer ou créer le vinyle
        $vinyl = $this->vinylRepository->findOneBy(['discogsId' => (string) $discogsVinylId]);

        if (!$vinyl) {
            // Créer un vinyle par défaut si pas trouvé
            $vinyl = new Vinyl();
            $vinyl->setDiscogsId((string) $discogsVinylId);
            $vinyl->setTitle('Vinyle #' . $discogsVinylId);
            $vinyl->setArtist('Artiste inconnu');
            $this->entityManager->persist($vinyl);
            $this->entityManager->flush();
        }

        // Récupérer le magasin
        $store = $this->storeRepository->find($storeId);
        if (!$store) {
            throw new \Exception('Le magasin sélectionné n\'existe pas.');
        }

        // Créer la réservation (sans vérifier le stock pour Discogs)
        $reservation = new Reservation();
        $reservation->setClient($user);
        $reservation->setVinyl($vinyl);
        $reservation->setStore($store);
        $reservation->setQuantity($quantity);
        $reservation->setStatus('pending');
        $reservation->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $reservation;
    }

    /**
     * Crée une réservation pour un utilisateur
     *
     * @throws \Exception si le stock est insuffisant
     */
    public function createReservation(
        User $user,
        Vinyl $vinyl,
        Store $store,
        int $quantity = 1
    ): Reservation {
        if ($quantity <= 0) {
            throw new \Exception('La quantité doit être supérieure à 0.');
        }

        // Vérifier le stock disponible
        $stock = $this->stockRepository->findOneBy(['vinyl' => $vinyl, 'store' => $store]);
        if (!$stock || $stock->getQuantity() < $quantity) {
            throw new \Exception('Le stock disponible est insuffisant pour cette réservation.');
        }

        // Créer la réservation
        $reservation = new Reservation();
        $reservation->setClient($user);
        $reservation->setVinyl($vinyl);
        $reservation->setStore($store);
        $reservation->setQuantity($quantity);
        $reservation->setStatus('pending');
        $reservation->setCreatedAt(new \DateTimeImmutable());

        // Diminuer le stock
        $stock->setQuantity($stock->getQuantity() - $quantity);

        $this->entityManager->persist($reservation);
        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return $reservation;
    }

    /**
     * Annule une réservation et restaure le stock
     */
    public function cancelReservation(Reservation $reservation): void
    {
        if ($reservation->getStatus() === 'cancelled') {
            throw new \Exception('Cette réservation est déjà annulée.');
        }

        // Restaurer le stock
        $stock = $this->stockRepository->findOneBy([
            'vinyl' => $reservation->getVinyl(),
            'store' => $reservation->getStore()
        ]);

        if ($stock && $reservation->getQuantity()) {
            $stock->setQuantity($stock->getQuantity() + $reservation->getQuantity());
            $this->entityManager->persist($stock);
        }

        $reservation->setStatus('cancelled');
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
    }

    /**
     * Valide une réservation (par le gérant du magasin)
     */
    public function validateReservation(Reservation $reservation): void
    {
        if ($reservation->getStatus() !== 'pending') {
            throw new \Exception('Seules les réservations en attente peuvent être validées.');
        }

        $reservation->setStatus('confirmed');
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
    }

    /**
     * Rejette une réservation (par le gérant du magasin)
     */
    public function rejectReservation(Reservation $reservation): void
    {
        if ($reservation->getStatus() !== 'pending') {
            throw new \Exception('Seules les réservations en attente peuvent être rejetées.');
        }

        $reservation->setStatus('rejected');
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
    }
}
