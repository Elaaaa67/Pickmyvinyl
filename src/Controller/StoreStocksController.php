<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Entity\Store;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/store-stocks', name: 'app_store_stocks_')]
class StoreStocksController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(StockRepository $stockRepo): Response
    {
        // Protège l'accès : utilisateur connecté seulement
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $store = $user->getStore();

        if (!$store) {
            $this->addFlash('error', 'Vous n\'avez pas d\'enseigne associée.');
            return $this->redirectToRoute('app_home');
        }

        // Récupère les stocks de cette enseigne
        $stocks = $stockRepo->findBy(['store' => $store]);

        return $this->render('store_stocks/index.html.twig', [
            'stocks' => $stocks,
            'store' => $store,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Stock $stock, Request $request, EntityManagerInterface $em): Response
    {
        // Protège l'accès : vérifier que c'est le propriétaire de l'enseigne
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        if ($stock->getStore()->getOwner() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce stock.');
        }

        if ($request->isMethod('POST')) {
            $quantity = (int) $request->request->get('quantity');

            if ($quantity < 0) {
                $this->addFlash('error', 'La quantité ne peut pas être négative.');
            } else {
                $stock->setQuantity($quantity);
                $em->flush();
                $this->addFlash('success', 'Stock mis à jour avec succès.');
                return $this->redirectToRoute('app_store_stocks_index');
            }
        }

        return $this->render('store_stocks/edit.html.twig', [
            'stock' => $stock,
        ]);
    }
}

