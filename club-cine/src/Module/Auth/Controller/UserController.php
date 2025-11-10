<?php

namespace App\Module\Auth\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Module\Auth\Entity\User;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/user')]
class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile', methods: ['GET'])]
    public function profile(): Response
    {
        // 1. Obtener el usuario autenticado
        $user = $this->getUser();

        // 2. Comprobar si el usuario es null (Debería ser un 401, pero es una buena práctica)
        if (!$user) {
            // Esto debería ser interceptado por el firewall, pero lo manejamos
            throw $this->createAccessDeniedException('Acceso denegado. Se requiere autenticación.');
        }

        // 3. ¡CRUCIAL! Asegurar que el objeto es nuestra entidad User
        if (!$user instanceof User) {
            // Esto puede ocurrir si el token es válido, pero el UserProvider devolvió algo genérico.
            // Es una buena práctica verificar el tipo de la entidad.
            // Para el flujo normal de JWT, $user DEBE ser una instancia de tu Entidad\User.
            return new JsonResponse(['message' => 'Tipo de usuario inesperado. Token inválido.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Si llegamos aquí, $user es definitivamente nuestra entidad User, y podemos
        // llamar a los métodos ORM (getId, getEmail, getName, etc.).
        return $this->render('home.html.twig', [
            'user' => $user,
        ]);
    }
}
