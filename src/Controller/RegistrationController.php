<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Store;
use App\Form\RegistrationFormType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{

    #[Route('/inscription/{type}', name: 'app_register', defaults: ['type' => 'user'])]
    public function register(
        string $type,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'user_type' => $type
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // Attribution des rôles selon le type
            if ($type === 'boutique') {
                $user->setRoles(['ROLE_STORE']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            // NE PAS vérifier automatiquement - l'utilisateur doit cliquer sur le lien
            $user->setIsVerified(false);

            // Générer un token unique de vérification
            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);

            // Crée automatiquement une enseigne (Store) UNIQUEMENT pour les boutiques
            if ($type === 'boutique') {
                $store = new Store();
                $store->setName($form->get('storeName')->getData() ?? $user->getEmail());
                $store->setAddress($form->get('storeAddress')->getData() ?? 'Non spécifiée');
                $store->setPhone($form->get('storePhone')->getData() ?? '');
                $store->setOwner($user);
                $user->setStore($store);
                $entityManager->persist($store);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoyer l'email de vérification
            try {
                $emailService->sendVerificationEmail($user, $verificationToken);
                $this->addFlash('success', 'Inscription réussie! Un email de vérification a été envoyé à votre adresse.');
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Inscription réussie, mais l\'email de vérification n\'a pas pu être envoyé.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'type' => $type
        ]);
    }
}
