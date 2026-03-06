<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\VinylRepository;
use App\Service\EmailService;
use App\Service\ReservationService;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();

        // Les utilisateurs normaux voient seulement leurs réservations
        // Les admins voient tout
        if ($this->isGranted('ROLE_ADMIN')) {
            $reservations = $reservationRepository->findAll();
        } else {
            $reservations = $reservationRepository->findBy(['client' => $user]);
        }

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, ReservationService $reservationService, CartService $cartService): Response
    {
        // Récupérer les items du panier
        $cart = $cartService->getCart();
        $cartItems = [];

        foreach ($cart['items'] as $vinylId => $item) {
            $cartItems[] = [
                'vinylId' => $vinylId,
                'title' => $item['title'] ?? 'Vinyle inconnu',
                'artist' => $item['artist'] ?? 'Artiste inconnu',
                'coverImage' => $item['coverImage'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
            ];
        }

        // Afficher le formulaire initial
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'cartItems' => $cartItems,
        ]);
    }
    #[Route('/create-multiple', name: 'app_reservation_create_multiple', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createMultiple(
        Request $request,
        ReservationService $reservationService,
        EmailService $emailService
    ): Response {
        try {
            $user = $this->getUser();
            if (!$user instanceof User) {
                throw new \Exception('Utilisateur non authentifié.');
            }

            $vinylId = (int) $request->request->get('vinylId');
            $quantity = (int) $request->request->get('quantity', 1);
            $storeId = (int) $request->request->get('storeId');

            if (!$vinylId || !$storeId) {
                $this->addFlash('error', 'Données manquantes.');
                return $this->redirectToRoute('app_reservation_new');
            }

            // Créer la réservation
            $createdReservation = $reservationService->createReservationFromDiscogs(
                $user,
                $vinylId,
                $storeId,
                $quantity
            );

            // Envoyer l'email de confirmation
            try {
                $emailService->sendReservationConfirmation($createdReservation);
            } catch (\Exception $e) {
                // L'email n'a pas pu être envoyé, mais la réservation est créée
                $this->addFlash('warning', 'Réservation créée mais l\'email de confirmation n\'a pas pu être envoyé.');
            }

            $this->addFlash('success', 'Réservation créée avec succès !');
            return $this->redirectToRoute('app_reservation_show', ['id' => $createdReservation->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('app_reservation_new');
        }
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Reservation $reservation): Response
    {
        $user = $this->getUser();

        // Vérifier l'accès :
        // - Le propriétaire de la réservation
        // - Un admin
        // - Le gérant du magasin concerné
        $isOwner = $user === $reservation->getClient();
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $isStoreManager = $this->isGranted('ROLE_STORE') && $user->getStore() === $reservation->getStore();

        if (!$isOwner && !$isAdmin && !$isStoreManager) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette réservation.');
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Reservation $reservation, ReservationService $reservationService): Response
    {
        // Vérifier que l'utilisateur est propriétaire ou admin
        if ($this->getUser() !== $reservation->getClient() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission d\'annuler cette réservation.');
        }

        try {
            $reservationService->cancelReservation($reservation);
            $this->addFlash('success', 'Votre réservation a été annulée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}

