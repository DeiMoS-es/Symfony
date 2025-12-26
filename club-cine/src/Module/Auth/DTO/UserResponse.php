<?php
namespace App\Module\Auth\DTO;

/**
 * DTO para representar al usuario en las respuestas de la aplicación.
 */
class UserResponse
{
    public string $email;
    public ?string $name;
    public ?string $groupId;
    public ?string $groupName;

    public function __construct(
        string $email, 
        ?string $name, 
        ?string $groupId = null, 
        ?string $groupName = null
    ) {
        $this->email = $email;
        $this->name = $name;
        $this->groupId = $groupId;
        $this->groupName = $groupName;
    }
}