<?php

namespace App\Module\Movie\Controller;

use App\Module\Movie\Service\TmdbService;
use App\Module\Auth\Entity\User;
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
        // 1. Obtenemos el usuario actual e indicamos al editor qué clase es
        /** @var User|null $user */
        $user = $this->getUser();

        // 2. Ahora el editor reconocerá getGroup() sin errores
        $group = ($user instanceof User) ? $user->getGroup() : null;
        
        // 3. Obtenemos la página de forma limpia usando el objeto Request
        $page = $request->query->getInt('page', 1);
        $catalog = $tmdbService->fetchPopularCatalog($page);

        return $this->render('dashboard.html.twig', [
            'movies' => $catalog,
            'group'  => $group,
        ]);
    }
}