<?php

namespace App\Module\Movie\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Module\Movie\DTO\MovieCatalogItemDTO;
use Psr\Log\LoggerInterface;

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

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('TMDb fetchPopular failed', ['status' => $response->getStatusCode()]);
            return [];
        }

        return $response->toArray();
    }


    public function fetchPopularCatalog(int $page = 1): array
    {
        $data = $this->fetchPopular($page);

        // Si la API falló devolvemos estructura vacía
        if (empty($data)) {
            return [
                'error' => 'No se pudo obtener datos de TMDb',
                'page' => $page,
                'total_pages' => 0,
                'total_results' => 0,
                'items' => []
            ];
        }

        $results = $data['results'] ?? [];

        $items = array_map(function ($movie) {
            $release = null;
            if (!empty($movie['release_date'])) {
                try {
                    $release = new \DateTimeImmutable($movie['release_date']);
                } catch (\Throwable $e) {
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
}
