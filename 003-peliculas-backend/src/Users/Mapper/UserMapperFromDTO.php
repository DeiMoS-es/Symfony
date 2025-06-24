<?php

namespace App\Users\Mapper;

use App\Users\Entity\Dto\UserInputDTO;
use App\Users\Entity\User;
/**
 * Este método se usa exclusivamente para transformar los datos que recibes desde el frontend.
 */
class UserMapperFromDTO
{

    public function fromInputDTO(UserInputDTO $dto): User
    {
        $user = new User();
        $user->setNombre($dto->nombre);
        $user->setApellidos($dto->apellidos);
        $user->setUserName($dto->userName);
        $user->setEmail($dto->email);
        $user->setPassword($dto->password); // recuerda aplicar hash más adelante
        $user->setImgUsuario($dto->imgUsuario ?? null);

        return $user;
    }
}
