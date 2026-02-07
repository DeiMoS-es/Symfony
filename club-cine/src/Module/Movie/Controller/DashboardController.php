<?php

namespace App\Module\Movie\Controller;

use App\Module\Movie\Service\MovieService; // <--- Cambiamos el service
use App\Module\Auth\Entity\User;
use App\Module\Auth\Mapper\UserMapper;
use App\Module\Group\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/movies')]
class DashboardController extends AbstractController
{

    #[Route('/dashboard', name: 'user_dashboard', methods: ['GET'])]
    public function dashboard(
        Request $request,
        MovieService $movieService,
        GroupRepository $groupRepository
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        // El Mapper ahora solo hace lo que debe: transformar datos básicos del usuario
        $userDto = $user ? UserMapper::toResponseDTO($user) : null;

        // Obtenemos los grupos de forma independiente y robusta
        $groups = $user ? $groupRepository->findGroupsByUser($user) : [];

        $searchTerm = $request->query->get('q', '');
        $page = $request->query->getInt('page', 1);
        $catalog = $movieService->getSearchCatalog($searchTerm, $page);

        return $this->render('dashboard.html.twig', [
            'movies'      => $catalog,
            'user'        => $userDto,
            'groups'      => $groups,
            'totalPages'  => $catalog['total_pages'] ?? 1,
            'currentPage' => $page,
            'searchTerm'  => $searchTerm,
        ]);
    }
}
