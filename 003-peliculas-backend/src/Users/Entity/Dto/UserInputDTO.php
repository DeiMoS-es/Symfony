<?php
namespace App\Users\Entity\Dto;

use Symfony\Component\Validator\Constraints as Assert;
/**
 * Este representa los datos que recibes desde el frontend
 */

class UserInputDTO
{
    //#[Assert\NotBlank(message: "El nombre es obligatorio")]
    public string $nombre;

    //#[Assert\NotBlank(message: "Los apellidos son obligatorios")]
    public string $apellidos;

    #[Assert\NotBlank(message: "El nombre de usuario es obligatorio")]
    #[Assert\Length(min: 4, max: 20, minMessage: "El nombre de usuario debe tener al menos {{ limit }} caracteres.")]
    public string $userName;

    #[Assert\NotBlank(message: "El email es obligatorio")]
    #[Assert\Email(message: "El email no es válido")]
    public string $email;

    #[Assert\NotBlank(message: "La contraseña es obligatoria")]
    #[Assert\Length(min: 6, minMessage: "La contraseña debe tener al menos {{ limit }} caracteres.")]
    public string $password;

    #[Assert\Length(max: 255)]
    public ?string $imgUsuario = null;
}

?>