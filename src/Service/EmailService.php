<?php

namespace App\Service;

use App\Entity\Reservation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    /**
     * Envoie un email de vérification à l'utilisateur
     */
    public function sendVerificationEmail(\App\Entity\User $user, string $verificationToken): void
    {
        $verificationUrl = 'http://localhost:8000/verify-email?token=' . $verificationToken;

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@pickmyvinyl.com', 'Pick My Vinyl'))
            ->to($user->getEmail())
            ->subject('Vérifiez votre adresse email')
            ->htmlTemplate('emails/verification.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl,
                'verificationToken' => $verificationToken,
            ]);

        $this->mailer->send($email);
    }

    /**
     * Envoie un email de confirmation de réservation
     */
    public function sendReservationConfirmation(Reservation $reservation): void
    {
        $user = $reservation->getClient();
        $vinyl = $reservation->getVinyl();
        $store = $reservation->getStore();

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@pickmyvinyl.com', 'Pick My Vinyl'))
            ->to($user->getEmail())
            ->subject('Confirmation de votre réservation #' . $reservation->getId())
            ->htmlTemplate('emails/reservation_confirmation.html.twig')
            ->context([
                'user' => $user,
                'reservation' => $reservation,
                'vinyl' => $vinyl,
                'store' => $store,
            ]);

        $this->mailer->send($email);
    }
}
