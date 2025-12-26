<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Entity\Group;
use App\Module\Group\Services\RecommendationFactory;
use App\Module\Movie\Service\MovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controlador para gestionar las recomendaciones dentro de un grupo de cine.
 */
class RecommendationController extends AbstractController
{
    /**
     * Esta ruta recibe el ID del grupo y el ID de TMDB.
     * Ejemplo: /group/5/recommend/12345
     */
    #[Route('/group/{id}/recommend/{tmdbId}', name: 'app_group_recommend_movie', methods: ['POST', 'GET'])]
    public function recommend(
        Group $group,
        int $tmdbId,
        MovieService $movieService,
        RecommendationFactory $factory,
        EntityManagerInterface $em
    ): Response {
        // 1. Usamos tu MovieService para asegurar que la peli está en nuestra DB local.
        // Si no está, la descarga de TMDB, le pone los géneros y la guarda.
        try {
            $movie = $movieService->getAndPersistFromTmdb($tmdbId);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Error al obtener la película de TMDB.');
            return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
        }

        // 2. Usamos la Factory para crear la "Recomendación" (la peli de la semana).
        // Le pasamos el grupo, la peli que acabamos de obtener y el usuario logueado.
        $recommendation = $factory->create(
            $group, 
            $movie, 
            $this->getUser()
        );

        // 3. Persistimos la recomendación en la base de datos.
        $em->persist($recommendation);
        $em->flush();

        // 4. Mensaje de éxito y volvemos a la página del grupo.
        $this->addFlash('success', sprintf('¡Genial! Has recomendado "%s".', $movie->getTitle()));

        return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
    }
}