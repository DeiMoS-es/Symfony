<?php
namespace App\Module\Auth\Mapper;

use App\Module\Auth\Entity\User;
use App\Module\Auth\DTO\UserResponse;
use App\Module\Auth\DTO\RegistrationRequest;
use Symfony\Component\HttpFoundation\Request;

class UserMapper
{
    public static function toResponseDTO(User $user): UserResponse
    {
        $groups = $user->getGroups();
        
        // Obtenemos el primer grupo si existe, o null si no pertenece a ninguno
        $firstGroup = $groups->first() ?: null;

        return new UserResponse(
            $user->getEmail(),
            $user->getName(),
            $firstGroup ? $firstGroup->getId()->toString() : null,
            $firstGroup ? $firstGroup->getName() : null
        );
    }

    public static function fromRequest(Request $request): RegistrationRequest
    {
        $dto = new RegistrationRequest();
        $dto->email = $request->request->get('email', '');
        $dto->name = $request->request->get('name', '');
        $dto->plainPassword = $request->request->get('password', '');
        
        return $dto;
    }
}