<?php
namespace App\Module\Movie\DTO;

/**
 * DTO inmutable para "hidratar" un item de una lista de películas de TMDB.
 */
final readonly class TmdbMovieListItemDTO
{
    public int $id;
    public ?string $title;
    public ?string $overview;
    public ?string $poster_path;
    public ?string $release_date;
    /** @var int[]|null */
    public ?array $genre_ids; // IDs de los géneros

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->title = $data['title'] ?? $data['original_title'] ?? null;
        $this->overview = $data['overview'] ?? null;
        $this->poster_path = $data['poster_path'] ?? null;
        $this->release_date = $data['release_date'] ?? null;
        
        // Aseguramos que genre_ids sea un array de enteros
        if (isset($data['genre_ids']) && is_array($data['genre_ids'])) {
             $this->genre_ids = array_map('intval', $data['genre_ids']);
        } else {
            $this->genre_ids = null;
        }
    }
}