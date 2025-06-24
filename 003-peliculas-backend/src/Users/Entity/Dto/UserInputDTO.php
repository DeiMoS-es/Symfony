<?php
namespace App\Users\Entity\Dto;
/**
 * Este representa los datos que recibes desde el frontend
 */
class UserInputDTO{

    public string $nombre;
    public string $apellidos;
    public string $userName;
    public string $email;
    public string $password;
    public ?string $imgUsuario;

}
?>