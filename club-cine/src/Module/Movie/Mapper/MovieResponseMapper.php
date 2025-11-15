<?php

namespace App\Module\Movie\Mapper;

use App\Module\Movie\DTO\MovieResponseDTO;
use App\Module\Movie\Entity\Movie;

/**
 * Servicio Mapper para convertir Entidades Movie en DTOs de respuesta.
 * Esto es útil para desacoplar la entidad de la vista (Twig).
 */
class MovieResponseMapper
{
    /**
     * Convierte una única entidad Movie en un DTO.
     */
    public function mapToDTO(Movie $movie): MovieResponseDTO
    {
        // Usamos el factory estático que ya definiste en tu DTO.
        // El mapper actúa como un punto de entrada de servicio limpio.
        return MovieResponseDTO::fromEntity($movie);
    }

    /**
     * Convierte una colección de entidades Movie en un array de DTOs.
     *
     * @param iterable<Movie> $movies
     * @return MovieResponseDTO[]
     */
    public function mapToDTOs(iterable $movies): array
    {
        $dtos = [];
        foreach ($movies as $movie) {
            $dtos[] = $this->mapToDTO($movie);
        }
        return $dtos;
    }
}