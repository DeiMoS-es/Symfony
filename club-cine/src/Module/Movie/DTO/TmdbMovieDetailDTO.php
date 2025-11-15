<?php
namespace App\Module\Movie\DTO;

/**
 * DTO inmutable para "hidratar" la respuesta de detalle de la API de TMDB.
 * No usamos promoción de propiedades aquí porque necesitamos lógica
 * de casting, fallbacks y mapeo en el constructor.
 */
final readonly class TmdbMovieDetailDTO
{
    public int $id;
    public ?string $title;
    public ?string $overview;
    public ?string $poster_path;
    public ?string $release_date;
    /** @var string[]|null */
    public ?array $genres; // Nombres de los géneros
    public ?int $runtime;
    public ?string $original_language;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->title = $data['title'] ?? $data['original_title'] ?? null;
        $this->overview = $data['overview'] ?? null;
        $this->poster_path = $data['poster_path'] ?? null;
        $this->release_date = $data['release_date'] ?? null;
        $this->runtime = isset($data['runtime']) ? (int)$data['runtime'] : null;
        $this->original_language = $data['original_language'] ?? null;

        $this->genres = null;
        if (!empty($data['genres']) && is_array($data['genres'])) {
            // Mapeamos para extraer solo el 'name' de cada género
            $this->genres = array_map(
                fn($g) => $g['name'] ?? null,
                $data['genres']
            );
            // Filtramos por si alguno vino null
            $this->genres = array_filter($this->genres); 
        }
    }
}