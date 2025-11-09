<?php
namespace App\Module\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest
{
    #[Assert\NotBlank(message: "El email es obligatorio.")]
    #[Assert\Email(message: "El email '{{ value }}' no es un email válido.")]
    private string $email;

    #[Assert\NotBlank(message: "La contraseña es obligatoria.")]
    private string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
