<?php

namespace App\Module\Movie\Controller;

use App\Module\Movie\Service\MovieService; // <--- Cambiamos el service
use App\Module\Auth\Entity\User;
use App\Module\Auth\Mapper\UserMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/movies')]
class DashboardController extends AbstractController
{

    #[Route('/dashboard', name: 'user_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, MovieService $movieService): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $userDto = $user ? UserMapper::toResponseDTO($user) : null;

        // 1. Capturamos el término de búsqueda
        $searchTerm = $request->query->get('q', '');
        
        $page = $request->query->getInt('page', 1);

        // 2. Usamos el nuevo método que creamos y testeamos
        $catalog = $movieService->getSearchCatalog($searchTerm, $page);

        return $this->render('dashboard.html.twig', [
            'movies'      => $catalog,
            'user'        => $userDto, 
            'totalPages'  => $catalog['total_pages'] ?? 1, 
            'currentPage' => $page,
            'searchTerm'  => $searchTerm, // 3. Lo pasamos a la vista
        ]);
    }
}