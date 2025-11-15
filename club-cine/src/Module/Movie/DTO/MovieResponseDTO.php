<?php
namespace App\Module\Movie\DTO;

use App\Module\Movie\Entity\Movie;
use App\Module\Movie\Entity\Genre; // Importar la entidad Genre
use Doctrine\Common\Collections\Collection; // Importar la Collection

/**
 * DTO inmutable para la respuesta de la API de películas.
 * Usamos "readonly" (PHP 8.2+) para asegurar que el DTO no se pueda modificar
 */
final readonly class MovieResponseDTO
{
    public ?string $releaseDate;
    public array $genres;

    public function __construct(
        // Propiedades promocionadas (se asignan automáticamente)
        public int $tmdbId,
        public string $title,
        public ?string $overview,
        public ?string $posterPath,
        
        // Propiedades que requieren lógica (no promocionadas)
        ?\DateTimeInterface $releaseDateInput,
        Collection $genresInput
    ) {
        // Lógica de transformación
        $this->releaseDate = $releaseDateInput ? $releaseDateInput->format('Y-m-d') : null;

        // Usar el método map() de la Collection es más limpio que ->toArray() y array_map()
        // Esto siempre devolverá un array, ej: ["Acción", "Aventura"] o []
        $this->genres = $genresInput->map(fn(Genre $g) => $g->getName())->toArray();
    }

    public static function fromEntity(\App\Module\Movie\Entity\Movie $movie): self
    {
        return new self(
            $movie->getTmdbId(),
            $movie->getTitle(),
            $movie->getOverview(),
            $movie->getPosterPath(),
            $movie->getReleaseDate(),
            $movie->getGenres()
        );
    }
}