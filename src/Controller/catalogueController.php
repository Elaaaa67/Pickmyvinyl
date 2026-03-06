<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\VinylRepository;
use Calliostro\Discogs\DiscogsClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class catalogueController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalog')]
    // src/Controller/catalogueController.php

    public function index(Request $request, DiscogsClient $client): Response
    {
        $queryId = $request->query->get('id');
        if (!empty($queryId)) {
            return $this->redirectToRoute('app_vinyl_detail', ['id' => (int) $queryId]);
        }

        // Récupère les paramètres de pagination
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = min(100, max(1, (int) $request->query->get('per_page', 50)));

        try {
            // 1. On récupère les vinyles avec pagination
            $results = $client->search(
                type: 'release',
                format: 'vinyl',
                perPage: $perPage,
                page: $page,

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

            // 3. ON DÉFINIT LES TOP SELLERS (On prend les 6 premiers)
            $topSellers = array_slice($vinyls, 0, 6);

            // 4. Récupère les informations de pagination
            $pagination = $results['pagination'] ?? [];

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur API : ' . $e->getMessage());
            $vinyls = [];
            $topSellers = [];
            $pagination = [];
        }

        return $this->render('catalog/index.html.twig', [
            'vinyls' => $vinyls,
            'topSellers' => $topSellers,
            'pagination' => $pagination,
            'currentPage' => $page,
            'perPage' => $perPage,
        ]);
    }
    #[Route('/catalogue/{id}', name: 'app_vinyl_detail')]
    public function detail(int $id, DiscogsClient $client): Response
    {
        try {
            // On force l'ID en entier et on appelle la release
            $release = $client->getRelease($id);

            // Si tu veux voir ce que l'API renvoie pour adapter ton Twig :
            // dd($release);

        } catch (\Exception $e) {
            // En cas d'erreur, on affiche l'erreur réelle pour débugger
            throw $this->createNotFoundException("Discogs dit : " . $e->getMessage());
        }

        return $this->render('catalog/detail.html.twig', [
            'vinyl' => $release,
        ]);
    }
    private function guessArtistFromTitle(string $title): ?string
    {
        // Tentative simple pour extraire "Artist - Title" si le titre est au format "Artist - Release".
        if (strpos($title, ' - ') !== false) {
            $parts = explode(' - ', $title, 2);
            return trim($parts[0]);
        }

        return null;
    }
}
