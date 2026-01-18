<?php

namespace App\Module\Movie\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Module\Movie\DTO\MovieCatalogItemDTO;
use Psr\Log\LoggerInterface;
use App\Module\Movie\Exception\TmdbException;
use App\Module\Movie\Exception\TmdbUnauthorizedException;
use App\Module\Movie\Exception\TmdbNotFoundException;
use App\Module\Movie\Exception\TmdbUnavailableException;

class TmdbService
{
    private const BASE = 'https://api.themoviedb.org/3';

    public function __construct(
        private HttpClientInterface $http,
        private string $apiKey,
        private ?string $readToken,
        private LoggerInterface $logger
    ) {}

    private function headers(): array
    {
        $headers = ['Accept' => 'application/json'];
        if ($this->readToken) {
            // Bearer token optional
            $headers['Authorization'] = 'Bearer ' . $this->readToken;
        }
        return $headers;
    }

    /**
     * Obtiene películas populares (paginated)
     */
    public function fetchPopular(int $page = 1): array
    {
        $url = sprintf('%s/movie/popular', self::BASE);
        $response = $this->http->request('GET', $url, [
            'headers' => $this->headers(),
            'query' => ['api_key' => $this->apiKey, 'page' => $page, 'language' => 'es-ES']
        ]);

        $status = $response->getStatusCode();

        if ($status === 401) {
            throw new TmdbUnauthorizedException("API key inválida o token incorrecto");
        }
        if ($status === 404) {
            throw new TmdbNotFoundException("Endpoint no encontrado en TMDb");
        }
        if ($status >= 500) {
            throw new TmdbUnavailableException("TMDb está temporalmente no disponible");
        }
        if ($status !== 200) {
            throw new TmdbException("Error inesperado de TMDb (status $status)");
        }

        return $response->toArray();
    }


    public function fetchPopularCatalog(int $page = 1): array
    {
        $data = $this->fetchPopular($page);

        if (empty($data)) {
            return $this->emptyResponse($page);
        }

        return $this->mapToCatalogDTO($data, $page);
    }


    /**
     * Obtener detalle de película por tmdbId
     */
    public function fetchMovie(int $tmdbId): ?array
    {
        $url = sprintf('%s/movie/%d', self::BASE, $tmdbId);
        $response = $this->http->request('GET', $url, [
            'headers' => $this->headers(),
            'query' => ['api_key' => $this->apiKey, 'language' => 'es-ES']
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->warning('TMDb fetchMovie failed', ['tmdbId' => $tmdbId, 'status' => $response->getStatusCode()]);
            return null;
        }

        return $response->toArray();
    }

    /**
     * Extra: buscar por query
     */
    public function search(string $query, int $page = 1): array
    {
        $url = sprintf('%s/search/movie', self::BASE);
        $response = $this->http->request('GET', $url, [
            'headers' => $this->headers(),
            'query' => ['api_key' => $this->apiKey, 'query' => $query, 'page' => $page, 'language' => 'es-ES']
        ]);

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return $response->toArray();
    }

    /**
     * Nuevo buscador que devuelve DTOs (Lo que el test espera)
     */
    public function searchCatalog(string $query, int $page = 1): array
    {
        $url = sprintf('%s/search/movie', self::BASE);
        $response = $this->http->request('GET', $url, [
            'headers' => $this->headers(),
            'query' => [
                'api_key' => $this->apiKey,
                'query' => $query,
                'page' => $page,
                'language' => 'es-ES'
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            return $this->emptyResponse($page);
        }

        return $this->mapToCatalogDTO($response->toArray(), $page);
    }

    /**
     * MÉTODO PRIVADO REUTILIZABLE (DRY - Don't Repeat Yourself)
     * Convierte la respuesta cruda de TMDB en nuestro formato de DTOs
     */
    private function mapToCatalogDTO(array $data, int $page): array
    {
        $results = $data['results'] ?? [];

        $items = array_map(function ($movie) {
            $release = null;
            if (!empty($movie['release_date'])) {
                try {
                    $release = new \DateTimeImmutable($movie['release_date']);
                } catch (\Throwable) {
                    // Si la fecha falla, se queda como null
                }
            }
            return new MovieCatalogItemDTO(
                (int) $movie['id'],
                $movie['title'] ?? 'Untitled',
                $movie['poster_path'] ?? null,
                $release
            );
        }, $results);

        return [
            'page' => $data['page'] ?? $page,
            'total_pages' => $data['total_pages'] ?? 0,
            'total_results' => $data['total_results'] ?? 0,
            'items' => $items
        ];
    }

    private function emptyResponse(int $page): array
    {
        return [
            'page' => $page,
            'total_pages' => 0,
            'total_results' => 0,
            'items' => []
        ];
    }
}
