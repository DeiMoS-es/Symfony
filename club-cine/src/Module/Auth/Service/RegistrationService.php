<?php
namespace App\Module\Auth\Service;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\DTO\UserResponse;
use App\Module\Auth\Mapper\UserMapper;
use App\Module\Auth\Entity\User;
use App\Module\Auth\Repository\UserRepository;
use App\Module\Auth\Exception\UserAlreadyExistsException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    /**
     * Registra un nuevo usuario.
     *
     * @throws UserAlreadyExistsException
     */
    public function register(RegistrationRequest $request): UserResponse
    {
        // 1️⃣ Validar DTO
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        // 2️⃣ Comprobar si el email ya existe
        $existingUser = $this->userRepository->findOneByEmail($request->email);
        if ($existingUser !== null) {
            throw new UserAlreadyExistsException($request->email);
        }

        // 3️⃣ Hashear la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword(
            new User($request->email, '', ['ROLE_USER']), // temporal para hashear
            $request->plainPassword
        );

        // 4️⃣ Crear entidad User
        $user = new User($request->email,$hashedPassword,['ROLE_USER']);
        $user->setName($request->name);

        // 5️⃣ Persistir usuario
        $this->userRepository->save($user, true);

        // 6️⃣ Devolver DTO de respuesta
        return UserMapper::toResponseDTO($user);
    }
}
