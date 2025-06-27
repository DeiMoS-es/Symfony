<?php
namespace App\Users\Controller;

use App\Users\Service\UserService;
use App\Users\Entity\Dto\UserInputDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['password'])) {
            throw new BadRequestHttpException('Faltan campos obligatorios.');
        }

        // Creamos el DTO desde el array
        $dto = new UserInputDTO();
        $dto->email = $data['email'];
        $dto->password = $data['password'];
        $dto->nombre = $data['nombre'] ?? '';
        $dto->apellidos = $data['apellidos'] ?? '';
        $dto->userName = $data['userName'] ?? '';
        $dto->imgUsuario = $data['imgUsuario'] ?? null;

        // Intentamos registrar al usuario
        try {
            $userOutput = $userService->createUserFromDto($dto);
            return new JsonResponse($userOutput, JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_CONFLICT);
        }
    }
}

?>