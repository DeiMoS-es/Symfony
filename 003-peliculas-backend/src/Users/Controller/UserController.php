<?php
namespace App\Users\Controller;

use App\Users\Service\UserService;
use App\Users\Entity\Dto\UserInputDTO;
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
    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request, UserService $userService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), UserInputDTO::class, 'json');
        $errors = $validator->validate($dto);

        if(count($errors) > 0){
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
}

?>