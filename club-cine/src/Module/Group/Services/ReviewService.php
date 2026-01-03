<?php

namespace App\Module\Group\Services;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Recommendation;
use App\Module\Group\Entity\Review;
use App\Module\Group\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReviewService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ReviewRepository $reviewRepository
    ) {}

    public function registerVote(Recommendation $recommendation, User $user, array $data): Review
    {
        // 1. Validar si ya existe un voto (Regla de negocio: Un voto por persona/película)
        $existing = $this->reviewRepository->findOneBy([
            'recommendation' => $recommendation,
            'user' => $user
        ]);

        if ($existing) {
            throw new \LogicException('Ya has emitido tu voto para esta película.');
        }

        // 2. Instanciar la entidad (aquí se ejecutan las validaciones internas de tu constructor)
        $review = new Review(
            $recommendation,
            $user,
            (int)$data['scoreScript'],
            (int)$data['scoreMainActor'],
            (int)$data['scoreMainActress'],
            (int)$data['scoreSecondaryActors'],
            (int)$data['scoreDirector'],
            $data['comment'] ?? null
        );

        $this->em->persist($review);
        $this->em->flush();

        return $review;
    }
}