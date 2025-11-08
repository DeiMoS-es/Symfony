<?php
namespace App\Module\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Module\Auth\Entity\User;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * DTO para el registro de un usuario.
 *
 * Notas:
 *  - Aquí guardamos la contraseña *en claro* (plainPassword) sólo temporalmente durante la
 *    validación/registro: **no** se persiste. El servicio de registro deberá hashear esta contraseña
 *    antes de crear la entidad User.
 */
class RegistrationRequest
{
    #[Assert\NotBlank(message: "El email es obligatorio.")]
    #[Assert\Email(message: "El email '{{ value }}' no es válido.")]
    #[Assert\Length(
        max: 180,
        maxMessage: "El email no puede tener más de {{ limit }} caracteres."
    )]
    public string $email;

    #[Assert\NotBlank(message: "La contraseña es obligatoria.")]
    #[Assert\Length(
        min: 8,
        minMessage: "La contraseña debe tener al menos {{ limit }} caracteres.",
        max: 4096
    )]
    public string $plainPassword;

    #[Assert\Length(
        max: 255,
        maxMessage: "El nombre no puede tener más de {{ limit }} caracteres."
    )]
    public ?string $name = null;

    public function __construct(string $email = '', string $plainPassword = '', ?string $name = null)
    {
        $this->email = $email;
        $this->plainPassword = $plainPassword;
        $this->name = $name;
    }
}
