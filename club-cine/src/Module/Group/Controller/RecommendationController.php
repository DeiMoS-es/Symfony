<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\Recommendation;
use App\Module\Group\Services\RecommendationFactory;
use App\Module\Movie\Service\MovieService;
use App\Module\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RecommendationController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/group/{id}/recommend/{tmdbId}', name: 'app_group_recommend_movie')]
    public function recommend(
        string $id, 
        int $tmdbId,
        MovieService $movieService,
        RecommendationFactory $factory,
        EntityManagerInterface $em
    ): Response {
        try {
            /** @var User|null $user */
            $user = $this->getUser();
            if (!$user) {
                throw new \Exception("Usuario no autenticado.");
            }

            $group = $em->getRepository(Group::class)->find($id);
            if (!$group) {
                throw new \Exception("El grupo especificado no existe.");
            }

            // 1. Obtenemos la película (la crea si no existe en nuestra DB)
            $movie = $movieService->getAndPersistFromTmdb($tmdbId);

            // 2. VALIDACIÓN: ¿Ya existe esta película en este grupo?
            $existingRecommendation = $em->getRepository(Recommendation::class)->findOneBy([
                'group' => $group,
                'movie' => $movie
            ]);

            if ($existingRecommendation) {
                $this->addFlash('warning', 'Esta película ya ha sido recomendada en este club por ' . 
                    $existingRecommendation->getRecommendedBy()->getName());
                
                return $this->redirectToRoute('app_group_show', ['id' => $id]);
            }

            // 3. Si no existe, procedemos a crearla
            $recommendation = $factory->create($group, $movie, $user);

            $em->persist($recommendation);
            $em->flush();

            $this->addFlash('success', '¡Película añadida a la cartelera con éxito!');

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Error: ' . $e->getMessage());
            return $this->redirectToRoute('user_dashboard');
        }

        return $this->redirectToRoute('app_group_show', ['id' => $id]);
    }
}