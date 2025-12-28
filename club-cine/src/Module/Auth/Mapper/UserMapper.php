<?php
namespace App\Module\Auth\Mapper;

use App\Module\Auth\Entity\User;
use App\Module\Auth\DTO\UserResponse;

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
}