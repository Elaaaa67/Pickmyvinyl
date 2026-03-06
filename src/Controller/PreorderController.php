<?php

namespace App\Controller;

use App\Entity\Preorder;
use App\Entity\Vinyl;
use App\Repository\PreorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/preorder', name: 'app_preorder_')]
class PreorderController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(PreorderRepository $preorderRepo): Response
    {
        // Affiche les pré-ventes disponibles (publiques)
        $preorders = $preorderRepo->findAll();

        return $this->render('preorder/index.html.twig', [
            'preorders' => $preorders,
        ]);
    }

    #[Route('/my', name: 'my_list', methods: ['GET'])]
    public function myList(PreorderRepository $preorderRepo): Response
    {
        // Affiche mes pré-commandes (utilisateur connecté seulement)
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        $preorders = $preorderRepo->findByUser($user);

        return $this->render('preorder/my_list.html.twig', [
            'preorders' => $preorders,
        ]);
    }

    #[Route('/new/{vinyl_id}', name: 'new', methods: ['POST'])]
    public function new(int $vinyl_id, EntityManagerInterface $em, Request $request): Response
    {
        // Crée une nouvelle pré-commande (utilisateur connecté seulement)
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        $vinyl = $em->getRepository(Vinyl::class)->find($vinyl_id);
        if (!$vinyl) {
            $this->addFlash('error', 'Vinyle non trouvé.');
            return $this->redirectToRoute('app_preorder_index');
        }

        // Vérifie si l'utilisateur a déjà pré-commandé ce vinyle
        $existing = $em->getRepository(Preorder::class)->findOneBy([
            'user' => $user,
            'vinyl' => $vinyl,
            'status' => 'pending',
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Vous avez déjà pré-commandé ce vinyle.');
            return $this->redirectToRoute('app_preorder_my_list');
        }

        $preorder = new Preorder();
        $preorder->setVinyl($vinyl);
        $preorder->setUser($user);
        $preorder->setStatus('pending');

        $em->persist($preorder);
        $em->flush();

        $this->addFlash('success', 'Pré-commande créée avec succès !');
        return $this->redirectToRoute('app_preorder_my_list');
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'])]
    public function cancel(Preorder $preorder, EntityManagerInterface $em, Request $request): Response
    {
        // Annule une pré-commande (seulement le propriétaire)
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if ($preorder->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette pré-commande.');
        }

        $preorder->setStatus('cancelled');
        $em->flush();

        $this->addFlash('success', 'Pré-commande annulée.');
        return $this->redirectToRoute('app_preorder_my_list');
    }
}

