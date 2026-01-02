<?php

namespace App\Module\Group\Services;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\Recommendation;
use App\Module\Group\Repository\RecommendationRepository;
use App\Module\Group\Repository\ReviewRepository;
use App\Module\Movie\Service\MovieService;

class RecommendationManager
{
    public function __construct(
        private readonly RecommendationRepository $recommendationRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly RecommendationFactory $factory,
        private readonly MovieService $movieService
    ) {}

    /**
     * Crea una nueva recomendación validando si ya existe en cartelera.
     */
    public function createRecommendation(Group $group, int $tmdbId, User $user): Recommendation
    {
        // 1. Obtenemos la película desde TMDB o DB local
        $movie = $this->movieService->getAndPersistFromTmdb($tmdbId);

        // 2. Validamos si ya existe una recomendación ABIERTA para esta película en este grupo
        $existing = $this->recommendationRepository->findOneBy([
            'group' => $group,
            'movie' => $movie,
            'status' => 'OPEN'
        ]);

        if ($existing) {
            throw new \LogicException('Esta película ya está en cartelera y está pendiente de votación.');
        }

        // 3. Fabricamos la entidad usando la Factory
        $recommendation = $this->factory->create($group, $movie, $user);

        // 4. Persistimos
        $this->recommendationRepository->save($recommendation);

        return $recommendation;
    }

    /**
     * Procesa todas las recomendaciones cuya fecha límite ha pasado.
     */
    public function processExpiredRecommendations(): int
    {
        $expired = $this->recommendationRepository->findExpiredToClose();
        $processedCount = 0;

        foreach ($expired as $recommendation) {
            $this->calculateAndClose($recommendation);
            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Calcula las medias de las 5 categorías y cierra la recomendación.
     */
    private function calculateAndClose(Recommendation $recommendation): void
    {
        $reviews = $this->reviewRepository->findByRecommendation($recommendation);
        $total = count($reviews);

        if ($total > 0) {
            $sums = [
                'script' => 0,
                'mainActor' => 0,
                'mainActress' => 0,
                'secondary' => 0,
                'director' => 0,
                'total' => 0
            ];

            foreach ($reviews as $review) {
                $sums['script'] += $review->getScoreScript();
                $sums['mainActor'] += $review->getScoreMainActor();
                $sums['mainActress'] += $review->getScoreMainActress();
                $sums['secondary'] += $review->getScoreSecondaryActors();
                $sums['director'] += $review->getScoreDirector();
                $sums['total'] += $review->getAverageScore();
            }

            $recommendation->closeWithStats(
                $sums['total'] / $total, // Nota media final
                $total,                  // Total de votos
                [
                    'script' => $sums['script'] / $total,
                    'mainActor' => $sums['mainActor'] / $total,
                    'mainActress' => $sums['mainActress'] / $total,
                    'secondary' => $sums['secondary'] / $total,
                    'director' => $sums['director'] / $total,
                ]
            );
        } else {
            // Si nadie votó, cerramos con valores a cero
            $recommendation->closeWithStats(0, 0, []);
        }

        $this->recommendationRepository->save($recommendation);
    }
}
