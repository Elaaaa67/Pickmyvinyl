<?php

declare(strict_types=1);

namespace App\Controller;

use Calliostro\Discogs\DiscogsClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VinylDetailController extends AbstractController
{
    // src/Controller/CatalogController.php

    #[Route('/catalogue/detail', name: 'app_vinyl_detail')]    public function detail(Request $request, DiscogsClient $client): Response
    {
        // Support both path parameter (/catalogue/123) and query parameter (?id=123)
        $id = $request->query->get('id');

        if (!$id) {
            // Si quelqu'tente d'accéder à /catalogue/detail sans id
            return $this->redirectToRoute('app_catalog');
        }

        try {
            $release = $client->getRelease((int)$id);
        } catch (\Exception $e) {
            throw $this->createNotFoundException("Vinyle introuvable.");
        }

        return $this->render('catalog/detail.html.twig', [
            'vinyl' => $release,
        ]);
    }
}
