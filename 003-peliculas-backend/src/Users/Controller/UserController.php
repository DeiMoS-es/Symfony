<?php

namespace App\Users\Controller;

use App\Users\Service\UserService;
use App\Users\Entity\Dto\UserInputDTO;
use App\Users\Service\UserMovieService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request, UserService $userService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), UserInputDTO::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];

            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $field = $error->getPropertyPath();
                $message = $error->getMessage();
                $errorMessages[$field] = $message;
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $userOutputDTO = $userService->createUserFromDto($dto);

        return new JsonResponse($userOutputDTO, Response::HTTP_CREATED);
    }

    #[Route('/delete/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse{
        $succes = $this->userService->deleteUserById($id);

        if(!$succes){
            return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse((['message' => 'Usuario eliminado correctamente']), Response::HTTP_OK);
    }
    

    #[Route('/{userId}/movies/{movieId}/rate', name: 'user_rate_movie', methods: ['POST'])]
    public function rateMovie(int $userId,int $movieId,Request $request,UserMovieService $userMovieService ): JsonResponse {

        $data = json_decode($request->getContent(), true);
        $rating = $data['rating'] ?? null;

        if (!$rating || $rating < 1 || $rating > 10) {
            return new JsonResponse([
                'error' => 'La puntuación debe estar entre 1 y 10'
            ], Response::HTTP_BAD_REQUEST);
        }

        $userMovieService->rateMovieByUser($userId, $movieId, (int)$rating);

        return new JsonResponse([
            'message' => 'Película puntuada correctamente'
        ], Response::HTTP_OK);
    }


}
