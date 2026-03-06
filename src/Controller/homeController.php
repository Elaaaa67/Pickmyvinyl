<?php

declare(strict_types=1);

namespace App\Controller;

use Calliostro\Discogs\DiscogsClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class homeController extends AbstractController
{

    #[Route('/', name: 'app_home')]public function index(Request $request, DiscogsClient $client): Response
    {
        $queryId = $request->query->get('id');
        if (!empty($queryId)) {
            return $this->redirectToRoute('app_vinyl_detail', ['id' => (int) $queryId]);
        }

        try {
            // 1. On récupère les 30 vinyles (Recherche globale)
            $results = $client->search(
                type: 'release',
                format: 'vinyl',
                perPage: 30,
            );

            // 2. On prépare les données pour Twig
            $vinyls = [];
            foreach (($results['results'] ?? []) as $item) {
                $fullTitle = $item['title'] ?? 'Artiste - Titre';
                $parts = explode(' - ', $fullTitle, 2);

                $vinyls[] = [
                    'id' => $item['id'] ?? null,
                    'title' => $parts[1] ?? $fullTitle,
                    'artist' => $parts[0] ?? 'Artiste inconnu',
                    'coverImage' => $item['cover_image'] ?? $item['thumb'] ?? null,
                    'year' => $item['year'] ?? 'N/A'
                ];
            }

            // 3. ON DÉFINIT ENFIN LES TOP SELLERS (On prend les 6 premiers par exemple)
            $topSellers = array_slice($vinyls, 0, 6);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur API : ' . $e->getMessage());
            $vinyls = [];
            $topSellers = [];
        }

        return $this->render('index.html.twig', [
            'vinyls' => $vinyls,
            'topSellers' => $topSellers, // Maintenant elle existe !
        ]);
    }


}
