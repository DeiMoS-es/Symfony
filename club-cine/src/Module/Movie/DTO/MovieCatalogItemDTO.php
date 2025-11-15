<?php 

namespace App\Module\Movie\DTO;

final readonly class MovieCatalogItemDTO
{
    public function __construct(
        public int $tmdbId,
        public string $title,
        public ?string $posterPath = null,
        public ?\DateTimeImmutable $releaseDate = null
    ) {}
}
