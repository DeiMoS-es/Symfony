<?php

namespace App\Module\Group\Services;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\Recommendation;
use App\Module\Movie\Entity\Movie;

/**
 * Esta clase se encarga de "fabricar" recomendaciones con los valores por defecto
 */
class RecommendationFactory
{
    /**
     * Crea una nueva recomendación vinculando un grupo, una película y el usuario que la propone.
     */
    public function create(Group $group, Movie $movie, User $user): Recommendation
    {
        // 1. Calculamos la fecha límite de votación. 
        // Por defecto, damos 7 días desde hoy para votar.
        $deadline = new \DateTimeImmutable('+7 days');

        // 2. Instanciamos la entidad. 
        // El constructor de Recommendation recibirá estos objetos y pondrá el status a 'OPEN'.
        return new Recommendation(
            $group,
            $movie,
            $user,
            $deadline
        );
    }
}