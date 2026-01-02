<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Entity\Group;
use App\Module\Group\Services\RecommendationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RecommendationController extends AbstractController
{
    public function __construct(
        private readonly RecommendationManager $recommendationManager
    ) {}

    /**
     * Ruta para recomendar una película a un club.
     * Utiliza ParamConverter para inyectar automáticamente el objeto Group por su ID.
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/group/{id}/recommend/{tmdbId}', name: 'app_group_recommend_movie')]
    public function recommend(Group $group, int $tmdbId): Response 
    {
        try {
            // Delegamos la lógica al Manager
            $this->recommendationManager->createRecommendation(
                $group, 
                $tmdbId, 
                $this->getUser()
            );

            $this->addFlash('success', '¡Película añadida a la cartelera con éxito!');
            
        } catch (\LogicException $e) {
            // Errores de negocio (ej: ya recomendada)
            $this->addFlash('warning', $e->getMessage());
        } catch (\Exception $e) {
            // Errores técnicos inesperados
            $this->addFlash('danger', 'Hubo un problema técnico al procesar la recomendación.');
        }

        // Redirigimos siempre a la vista del club
        return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
    }
}