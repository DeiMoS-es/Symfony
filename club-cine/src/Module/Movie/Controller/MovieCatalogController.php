<?php 

namespace App\Module\Movie\Controller;

use App\Module\Movie\Service\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class MovieCatalogController extends AbstractController
{
    #[Route('/catalog', name: 'movie_catalog', methods: ['GET'])]
    public function catalog(TmdbService $tmdbService): JsonResponse
    {
        $page = (int) ($_GET['page'] ?? 1);
        $catalog = $tmdbService->fetchPopularCatalog($page);

        return $this->json($catalog);
    }
}