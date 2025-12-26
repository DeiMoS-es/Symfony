<?php

namespace App\Module\Group\Services;

use App\Module\Group\Entity\Recommendation;
use App\Module\Group\Repository\RecommendationRepository;
use App\Module\Group\Repository\ReviewRepository;

class RecommendationManager
{
    public function __construct(
        private readonly RecommendationRepository $recommendationRepository,
        private readonly ReviewRepository $reviewRepository
    ) {}

    /**
     * Este es el método que llamarás desde un comando o tarea programada
     * para cerrar todas las películas cuya fecha límite haya pasado.
     */
    public function processExpiredRecommendations(): int
    {
        // Buscamos las que han caducado y siguen OPEN
        $expired = $this->recommendationRepository->findExpiredToClose();
        $processedCount = 0;

        foreach ($expired as $recommendation) {
            $this->calculateAndClose($recommendation);
            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Calcula las medias de las 5 categorías y la nota final
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

            // Usamos el método que creamos en la Entidad Recommendation
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
            // Si nadie votó, cerramos con todo a 0
            $recommendation->closeWithStats(0, 0, []);
        }

        $this->recommendationRepository->save($recommendation);
    }
}