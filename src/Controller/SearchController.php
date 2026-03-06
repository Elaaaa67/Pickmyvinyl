<?php

namespace App\Controller;

use Calliostro\Discogs\DiscogsClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function searchPage(Request $request, DiscogsClient $client)
    {
        $query = trim((string) $request->query->get('q', ''));
        $results = [];

        if (strlen($query) >= 3) {
            try {
                $response = $client->search(q: $query, type: 'release', format: 'vinyl', perPage: 50);
                $results = $response['results'] ?? [];
            } catch (\Throwable $e) {
                // En cas d'erreur API, on continue avec tableau vide
            }
        }

        return $this->render('search/results.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    #[Route('/api/search', name: 'api_search')]
    public function search(Request $request, DiscogsClient $client): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));

        // UX: ne pas interroger l'API pour moins de 3 caractères
        if (strlen($q) < 3) {
            return new JsonResponse([]);
        }

        try {
            $results = $client->search(q: $q, type: 'release', format: 'vinyl', perPage: 7);
        } catch (\Throwable $e) {
            // En cas d'erreur côté API, retourner tableau vide (ou adapter selon besoin)
            return new JsonResponse([], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $formatted = [];
        foreach ($results['results'] ?? [] as $item) {
            $formatted[] = [
                'id'    => $item['id'] ?? null,
                'title' => $item['title'] ?? '',
                'img'   => $item['thumb'] ?? $item['cover_image'] ?? null,
            ];
        }

        return new JsonResponse($formatted);
    }
}
