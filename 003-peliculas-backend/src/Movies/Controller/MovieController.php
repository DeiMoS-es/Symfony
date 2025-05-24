<?php

namespace App\Movies\Controller;

use App\Movies\Repository\MovieRepository;
use App\Movies\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class MovieController extends AbstractController
{
    private MovieService $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
    }

    #[Route('/movies/{id}', name: 'get_movie', methods: ['GET'])]
    public function getMovie(int $id, MovieRepository $movieRepository, SerializerInterface $serializer): JsonResponse {
        $movie = $movieRepository->find($id);

        if (!$movie) {
            return new JsonResponse(['error' => 'Película no encontrada'], 404);
        }

        $json = $serializer->serialize($movie, 'json', ['groups' => 'movie:read']);

        return new JsonResponse($json, 200, [], true);
    }

}
