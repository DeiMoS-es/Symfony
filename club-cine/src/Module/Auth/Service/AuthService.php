<?php
namespace App\Module\Auth\Service;

use App\Module\Auth\Repository\UserRepository;
use App\Module\Auth\Exception\InvalidCredentialsException;
use App\Module\Auth\Security\TokenGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Module\Auth\Entity\User;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenGeneratorInterface $tokenGenerator
    ) {}

    /**
     * Intenta autenticar al usuario por email+password.
     *
     * @param string $email
     * @param string $plainPassword
     * @return string Token (JWT u otro) si autenticación OK
     *
     * @throws InvalidCredentialsException si email inexistente o password incorrecto
     */
    public function loginUser(string $email, string $plainPassword): string
    {
        $user = $this->userRepository->findOneByEmail($email);

        if (!$user instanceof User) {
            // No revelamos si falta el email o la contraseña por seguridad
            throw new InvalidCredentialsException();
        }

        // Verificamos contraseña
        if (!$this->passwordHasher->isPasswordValid($user, $plainPassword)) {
            throw new InvalidCredentialsException();
        }

        // Generar token usando la interfaz desacoplada
        return $this->tokenGenerator->generateToken($user);
    }
}
