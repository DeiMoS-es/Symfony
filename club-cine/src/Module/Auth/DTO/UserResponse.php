<?php
namespace App\Module\Auth\DTO;

use Ramsey\Uuid\UuidInterface;

class UserResponse
{
    public string $email;
    public ?string $name;

    public function __construct(string $email, ?string $name)
    {
        $this->email = $email;
        $this->name = $name;
    }
}
