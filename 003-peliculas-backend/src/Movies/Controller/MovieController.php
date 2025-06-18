<?php

namespace App\Movies\Controller;


use App\Movies\Dto\MovieInputDto;
use App\Movies\Mapper\MovieMapperFromDTO;
use App\Movies\Mapper\MovieMapperToDTO;
use App\Movies\Repository\MovieRepository;
use App\Movies\Service\MovieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/movies')]
final class MovieController extends AbstractController
{
    private MovieService $movieService;
    private MovieMapperFromDTO $movieMapperFromDTO;
    private MovieMapperToDTO $movieMapperToDTO;
    private ValidatorInterface $validator;


    public function __construct(MovieService $movieService, MovieMapperFromDTO $movieMapperFromDTO, MovieMapperToDTO $movieMapperToDTO, ValidatorInterface $validator)
    {
        $this->movieService = $movieService;
        $this->movieMapperFromDTO = $movieMapperFromDTO;
        $this->movieMapperToDTO = $movieMapperToDTO;
        $this->validator = $validator;
    }
    // ruta para obtener todas las películas
    #[Route('/', name: 'app_movie_index', methods: ['GET'])]
    public function index(MovieRepository $movieRepository): Response
    {

        return $this->render('movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
            'controller_name' => 'MovieController',
        ]);
    }

    // ruta para obtener una película por su ID
    #[Route('/getMovie/{id}', name: 'get_movie', methods: ['GET'])]
    public function getMovie(int $id, SerializerInterface $serializer): JsonResponse
    {
        $movie = $this->movieService->getMovieById($id);

        if (!$movie) {
            return new JsonResponse(['error' => 'Película no encontrada'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($movie, 'json', ['groups' => 'movie:read']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    //ruta para obtener una película por su nombre
    #[Route('/search/{title}', name: 'search_movie', methods: ['GET'])]
    public function searchMovie(string $title, SerializerInterface $serializer): JsonResponse{
        $movie = $this->movieService->searchMovieByTitle($title);
        if(empty($movie)){
            return new JsonResponse(['error' => $title.' no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $json = $serializer->serialize(($movie), 'json', ['groups' => 'movie:read']);

        return new JsonResponse(($json), Response::HTTP_OK , [], true);
    }

    // ruta para crear una nueva película
    #[Route('/create', name: 'create_movie', methods: ['POST'])]
    public function createMovie(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $inputDto = $serializer->deserialize($request->getContent(), MovieInputDto::class, 'json');
        $errors = $this->validator->validate(($inputDto));

        if (count($errors) > 0) {
            return new JsonResponse(['error' => 'Datos inválidos', 'details' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // 🛠️ Delegar toda la lógica al servicio
        $outputDto = $this->movieService->createMovieFromDto($inputDto);

        // Serializar y responder
        return new JsonResponse($serializer->serialize($outputDto, 'json'), Response::HTTP_CREATED, [], true);
    }
}
