<?php
namespace App\Users\Mapper;

use App\Users\Entity\Dto\UserOutputDTO;
use App\Users\Entity\User;
/**
 * Este método se usa exclusivamente para respuestas hacia el frontend.
 */
class UserMapperToDTO{

    public function toOutputDTO(User $user): UserOutputDTO
    {
        $dto = new UserOutputDTO();
        $dto->email = $user->getEmail();
        $dto->nombre = $user->getNombre();
        $dto->apellidos = $user->getApellidos();
        $dto->userName = $user->getUserName();
        $dto->imgUsuario = $user->getImgUsuario();
        $dto->createdAt = $user->getCreatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
?>