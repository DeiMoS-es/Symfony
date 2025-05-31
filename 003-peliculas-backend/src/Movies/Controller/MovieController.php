<?php

namespace App\Movies\Controller;

use App\Movies\Repository\MovieRepository;
use App\Movies\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/movie')]
final class MovieController extends AbstractController
{
    private MovieService $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
    }

    #[Route('/', name: 'app_movie_index', methods: ['GET'])]
    public function index(MovieRepository $movieRepository): Response
    {

        return $this->render('movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
            'controller_name' => 'MovieController',
        ]);
    }

    #[Route('/movies/{id}', name: 'get_movie', methods: ['GET'])]
    public function getMovie(int $id, SerializerInterface $serializer): JsonResponse
    {
        $movie = $this->movieService->getMovieById($id);

        if (!$movie) {
            return new JsonResponse(['error' => 'Película no encontrada'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($movie, 'json', ['groups' => 'movie:read']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
