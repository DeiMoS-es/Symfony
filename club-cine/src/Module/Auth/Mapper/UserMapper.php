<?php
namespace App\Module\Auth\Mapper;

use App\Module\Auth\Entity\User;
use App\Module\Auth\DTO\UserResponse;

class UserMapper
{
    public static function toResponseDTO(User $user): UserResponse
    {
        $group = $user->getGroup();

        return new UserResponse(
            $user->getEmail(),
            $user->getName(),
            $group ? $group->getId()->toString() : null,
            $group ? $group->getName() : null
        );
    }
}