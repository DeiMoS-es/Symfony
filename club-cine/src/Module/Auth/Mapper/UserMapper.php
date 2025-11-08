<?php
namespace App\Module\Auth\Mapper;

use App\Module\Auth\Entity\User;
use App\Module\Auth\DTO\UserResponse;

class UserMapper
{
    public static function toResponseDTO(User $user): UserResponse
    {
        return new UserResponse(
            $user->getEmail(),
            $user->getName()
        );
    }
}
