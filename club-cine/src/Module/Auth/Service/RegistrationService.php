<?php

namespace App\Module\Auth\Service;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\DTO\UserResponse;
use App\Module\Auth\Mapper\UserMapper;
use App\Module\Auth\Entity\User;
use App\Module\Auth\Repository\UserRepository;
use App\Module\Auth\Exception\UserAlreadyExistsException;
use App\Module\Group\Service\GroupInvitationHandler; // Inyectamos el experto
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private GroupInvitationHandler $groupHandler // SOLID: Delegamos la lógica de grupos
    ) {}

    public function register(RegistrationRequest $request, ?string $token = null): UserResponse
    {
        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException("Datos de registro inválidos.");
        }

        $existingUser = $this->userRepository->findOneByEmail($request->email);
        if ($existingUser !== null) {
            throw new UserAlreadyExistsException($request->email);
        }

        $user = new User($request->email, '', ['ROLE_USER']);
        $user->setName($request->name);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $request->plainPassword);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        // Si hay token, usamos el Handler de Grupos
        // if ($token) {
        //     $invitation = $this->groupHandler->getValidInvitation($token);
        //     if ($invitation) {
        //         // El handler se encarga de TODO: añadir al grupo y borrar invitación
        //         $this->groupHandler->handleAcceptance($invitation, $user);
        //     }
        // }

        return UserMapper::toResponseDTO($user);
    }

    public function getInvitationEmail(?string $token): string
    {
        if (!$token) return '';

        // Delegamos también la búsqueda para que la lógica de "expirado" sea única
        $invitation = $this->groupHandler->getValidInvitation($token);
        return $invitation ? $invitation->getEmail() : '';
    }
}
?>