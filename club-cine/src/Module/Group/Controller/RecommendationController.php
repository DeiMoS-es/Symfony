<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Entity\Group;
use App\Module\Group\Services\RecommendationFactory;
use App\Module\Movie\Service\MovieService;
use App\Module\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class RecommendationController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/group/{id}/recommend/{tmdbId}', name: 'app_group_recommend_movie', methods: ['GET', 'POST'])]
    public function recommend(
        string $id, 
        int $tmdbId,
        MovieService $movieService,
        RecommendationFactory $factory,
        EntityManagerInterface $em
    ): Response {
        try {
            // 1. Verificamos el usuario logueado
            /** @var User|null $user */
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception("Debes iniciar sesión para realizar esta acción.");
            }

            // 2. Buscamos el grupo manualmente por su ID (string/UUID)
            $group = $em->getRepository(Group::class)->find($id);
            if (!$group) {
                throw new \Exception("El grupo de cine no existe.");
            }

            // 3. Obtenemos la película (y la guardamos en local si no existe)
            // Asegúrate de haber actualizado el campo releaseDate en la Entidad Movie a 'date_immutable'
            $movie = $movieService->getAndPersistFromTmdb($tmdbId);
            
            // 4. Creamos la recomendación usando la Factory
            $recommendation = $factory->create($group, $movie, $user);

            // 5. Guardamos en la base de datos
            $em->persist($recommendation);
            $em->flush();

            $this->addFlash('success', sprintf('¡Has recomendado "%s" con éxito!', $movie->getTitle()));

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
            return $this->redirectToRoute('user_dashboard');
        }

        // 6. Redirigimos a la cartelera del grupo
        return $this->redirectToRoute('app_group_show', ['id' => $id]);
    }
}