<?php
namespace App\Tmbd\Service;

interface TmdbService{
    
    public function fetchMovieFromTMDB();
    public function getMovie(int $id);
}
?>