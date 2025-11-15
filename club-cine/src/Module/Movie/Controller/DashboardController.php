<?php

namespace App\Module\Movie\Controller;

use App\Module\Movie\Service\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/movies')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard', methods: ['GET'])]
    public function dashboard(TmdbService $tmdbService): Response
    {
        $page = (int) ($_GET['page'] ?? 1);
        $catalog = $tmdbService->fetchPopularCatalog($page);

        return $this->render('dashboard.html.twig', [
            'movies' => $catalog,
        ]);
    }
}
