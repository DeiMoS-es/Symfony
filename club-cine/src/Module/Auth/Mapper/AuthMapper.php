<?php
namespace App\Module\Auth\Mapper;

use App\Module\Auth\DTO\LoginRequest;
use Symfony\Component\HttpFoundation\Request;

class AuthMapper
{
    public static function fromRequest(Request $request): LoginRequest
    {
        return new LoginRequest(
            (string) $request->request->get('email', ''),
            (string) $request->request->get('password', '')
        );
    }
}
?>