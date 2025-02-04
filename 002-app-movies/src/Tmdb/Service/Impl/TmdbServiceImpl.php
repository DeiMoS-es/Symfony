<?php
namespace App\Tmdb\Service\Impl;

use App\Tmdb\Service\TmdbService;
use GuzzleHttp\Client;

class TmdbServiceImpl implements TmdbService{
    
    private $client;

    public function __construct(){
        $this->client = new Client([
            'base_uri' => 'https://api.themoviedb.org/3/',
            'timeout'  => 2.0,
        ]);        
    }
    public function fetchMovieFromTMDB(){
        $response = $this->client->request('GET', 'discover/movie', [
            'query' => [
                'api_key' => '1e49c60de9f7c359f129c7f691c795cf',
                'language' => 'es-ES',
                'page' => 1
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data;
    }
}
?>