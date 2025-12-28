<?php

namespace App\Module\Movie\Controller;

use App\Module\Movie\Service\TmdbService;
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
    public function dashboard(Request $request, TmdbService $tmdbService): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        // 1. Convertimos la Entidad en un DTO limpio usando tu Mapper
        // Ahora $userDto tiene .email, .name, .groupId y .groupName
        $userDto = $user ? UserMapper::toResponseDTO($user) : null;

        $page = $request->query->getInt('page', 1);
        $catalog = $tmdbService->fetchPopularCatalog($page);
        return $this->render('dashboard.html.twig', [
            'movies' => $catalog,
            'user'   => $userDto, 
            'totalPages' => $catalog['total_pages'] ?? 1, 
            'currentPage' => $page,
        ]);
    }
}
