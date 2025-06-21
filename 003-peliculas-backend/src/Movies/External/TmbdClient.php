<?php

namespace App\Movies\External;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmbdClient
{

    private string $apiKey;
    private HttpClientInterface $client;
    private string $baseUrl = 'https://api.themoviedb.org/3/discover/movie';
    private string $languaje = 'es-ES';
    private string $region = 'ES';
    private string $imageU = 'https://image.tmdb.org/t/p/w500/';
    private string $imageBaseUrlOriginal = 'https://image.tmdb.org/t/p/original/';

    public function __construct(string $tmbdApiKey, HttpClientInterface $client)
    {
        $this->apiKey = $tmbdApiKey;
        $this->client = $client;
    }

    public function fetchAllMovies(): array
    {
        $allMovies = [];
        $page = 1;
        $totalPages = 1;
        $maxPages = 500;
        do {
            $response = $this->client->request('GET', $this->baseUrl, [
                'query' => [
                    'api_key' => $this->apiKey,
                    'languaje' => $this->languaje,
                    'region' => $this->region,
                    'page' => $page
                ],
            ]);
            $data = $response->toArray();
            $allMovies = array_merge($allMovies, $data['results']);
            $totalPages = min($data['total_pages'], $maxPages);
            $page++;
        } while ($page <= $totalPages);
        return $allMovies;
    }

    public function fetchGenres(): array
    {
        $response = $this->client->request('GET', 'https://api.themoviedb.org/3/genre/movie/list', [
            'query' => ['language' => 'es-ES', 'api_key' => $this->apiKey]
        ]);

        return $response->toArray()['genres'] ?? [];
    }
}
