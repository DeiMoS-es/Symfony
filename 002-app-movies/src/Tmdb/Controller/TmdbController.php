<?php
namespace App\Tmdb\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Tmdb\Service\TmdbService;

class TmdbController extends AbstractController{

    private $tmdbService;

    public function __construct(TmdbService $tmdbService){
        $this->tmdbService = $tmdbService;
    }
    #[Route('/tmdb', name: 'tmdb')]
    public function index(): Response{
        return $this->render('tmdb/index.html.twig', [
            'controller_name' => 'TmdbController',
        ]);
    }

    #[Route('/tmdb/movies', name: 'tmdb_movies', methods: ['GET'])]
    public function getMovies(): Response
    {
        $movies = $this->tmdbService->fetchMovieFromTMDB();
        return $this->json($movies);
    }
}
?>