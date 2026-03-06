<?php

namespace App\Service;

use App\Entity\Reservation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ReservationEmailService
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    /**
     * Envoyer un email de confirmation de réservation
     */
    public function sendReservationConfirmation(Reservation $reservation): void
    {
        $email = new TemplatedEmail();
        $email
            ->from(new Address('noreply@pickmyvinyl.local', 'PickMyVinyl'))
            ->to($reservation->getClient()->getEmail())
            ->subject('Confirmation de votre réservation de vinyle')
            ->htmlTemplate('emails/reservation_confirmation.html.twig')
            ->context([
                'reservation' => $reservation,
                'vinyl' => $reservation->getVinyl(),
                'store' => $reservation->getStore(),
                'client' => $reservation->getClient(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoyer un email de rappel avant retrait
     */
    public function sendRetrievalReminder(Reservation $reservation): void
    {
        $email = new TemplatedEmail();
        $email
            ->from(new Address('noreply@pickmyvinyl.local', 'PickMyVinyl'))
            ->to($reservation->getClient()->getEmail())
            ->subject('Rappel : Venez récupérer votre vinyle !')
            ->htmlTemplate('emails/retrieval_reminder.html.twig')
            ->context([
                'reservation' => $reservation,
                'vinyl' => $reservation->getVinyl(),
                'store' => $reservation->getStore(),
                'client' => $reservation->getClient(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoyer un email d'annulation de réservation
     */
    public function sendCancellationEmail(Reservation $reservation): void
    {
        $email = new TemplatedEmail();
        $email
            ->from(new Address('noreply@pickmyvinyl.local', 'PickMyVinyl'))
            ->to($reservation->getClient()->getEmail())
            ->subject('Votre réservation a été annulée')
            ->htmlTemplate('emails/reservation_cancellation.html.twig')
            ->context([
                'reservation' => $reservation,
                'vinyl' => $reservation->getVinyl(),
                'store' => $reservation->getStore(),
                'client' => $reservation->getClient(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoyer une notification au magasin pour une nouvelle réservation
     */
    public function notifyStoreNewReservation(Reservation $reservation): void
    {
        $storeOwner = $reservation->getStore()->getOwner();

        if (!$storeOwner) {
            return;
        }

        $email = new TemplatedEmail();
        $email
            ->from(new Address('noreply@pickmyvinyl.local', 'PickMyVinyl'))
            ->to($storeOwner->getEmail())
            ->subject('Nouvelle réservation dans votre magasin')
            ->htmlTemplate('emails/store_new_reservation.html.twig')
            ->context([
                'reservation' => $reservation,
                'vinyl' => $reservation->getVinyl(),
                'store' => $reservation->getStore(),
                'client' => $reservation->getClient(),
            ]);

        $this->mailer->send($email);
    }
}

