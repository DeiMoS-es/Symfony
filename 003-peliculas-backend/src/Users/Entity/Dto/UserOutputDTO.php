<?php

namespace App\Users\Entity\Dto;
/**
 * Este es el DTO que devuelves al frontend
 */
class UserOutputDTO
{

    public string $email;
    public array $roles;
    public ?string $imgUsuario;
    public string $nombre;
    public string $apellidos;
    public string $userName;
    public array $arrayRoles;
    public string $createdAt;   
}
