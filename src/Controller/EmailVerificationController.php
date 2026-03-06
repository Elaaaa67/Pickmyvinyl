<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmailVerificationController extends AbstractController
{
    #[Route('/verify-email', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $token = $request->query->get('token');

        if (!$token) {
            $this->addFlash('error', 'Token manquant.');
            return $this->redirectToRoute('app_login');
        }

        // Chercher l'utilisateur avec ce token
        $user = $userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier l'utilisateur
        $user->setIsVerified(true);
        $user->setVerificationToken(null); // Effacer le token après utilisation
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Email vérifié avec succès! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/email-not-verified', name: 'app_email_not_verified', methods: ['GET'])]
    public function emailNotVerified(): Response
    {
        $user = $this->getUser();

        return $this->render('email_verification/not_verified.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/resend-verification-email', name: 'app_resend_verification_email', methods: ['POST'])]
    public function resendVerificationEmail(
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Régénérer un token
        $verificationToken = bin2hex(random_bytes(32));
        $user->setVerificationToken($verificationToken);
        $entityManager->persist($user);
        $entityManager->flush();

        // Renvoyer l'email
        try {
            $emailService->sendVerificationEmail($user, $verificationToken);
            $this->addFlash('success', 'Email de vérification renvoyé avec succès!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email.');
        }

        return $this->redirectToRoute('app_email_not_verified');
    }
}
