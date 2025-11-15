<?php
namespace App\Module\Movie\DTO;

/**
 * DTO inmutable para la creación/actualización de películas.
 * Se puede construir a partir de la data de TMDB.
 */
final readonly class MovieUpsertRequest
{
    /**
     * @param int[]|string[]|null $genres 
     * - string[] (nombres) si viene de TmdbMovieDetailDTO
     * - int[] (ids) si viene de TmdbMovieListItemDTO
     */
    public function __construct(
        public int $tmdbId,
        public string $title,
        public ?string $overview = null,
        public ?string $posterPath = null,
        public ?\DateTimeImmutable $releaseDate = null,
        public ?array $genres = null
    ) {
        // La promoción de propiedades se encarga de todo
    }

    public static function fromTmdbDetailDTO(TmdbMovieDetailDTO $d): self
    {
        $release = null;
        if (!empty($d->release_date)) {
            try {
                // Usamos DateTimeImmutable directamente
                $release = new \DateTimeImmutable($d->release_date);
            } catch (\Throwable $e) {
                // La fecha era inválida, se queda en null
                $release = null; 
            }
        }

        return new self(
            $d->id,
            $d->title ?? 'Untitled',
            $d->overview,
            $d->poster_path,
            $release,
            $d->genres // Pasa el array de nombres de género
        );
    }

    public static function fromTmdbListItemDTO(TmdbMovieListItemDTO $i): self
    {
        $release = null;
        if (!empty($i->release_date)) {
            try {
                $release = new \DateTimeImmutable($i->release_date);
            } catch (\Throwable $e) {
                $release = null;
            }
        }

        // genres from list are ids — keep as ints
        return new self(
            $i->id,
            $i->title ?? 'Untitled',
            $i->overview,
            $i->poster_path,
            $release,
            $i->genre_ids // Pasa el array de IDs de género
        );
    }
}