<?php
namespace App\Module\Auth\Service;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\DTO\UserResponse;
use App\Module\Auth\Mapper\UserMapper;
use App\Module\Auth\Entity\User;
use App\Module\Auth\Repository\UserRepository;
use App\Module\Auth\Exception\UserAlreadyExistsException;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Group\Entity\GroupMember;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }

    /**
     * Registra un nuevo usuario.
     *
     * @throws UserAlreadyExistsException
     */
    public function register(RegistrationRequest $request, ?string $token = null): UserResponse
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

        // 3️⃣ Crear la entidad User PRIMERO (pero sin el password real aún)
        $user = new User($request->email, '', ['ROLE_USER']);
        $user->setName($request->name);

        // 4️⃣ Hashear la contraseña usando la entidad que acabamos de crear
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user, 
            $request->plainPassword
        );

        // 5️⃣ Actualizar la contraseña en la entidad
        $user->setPassword($hashedPassword);

        // 6️⃣ Persistir usuario (Asegúrate de que el repositorio tenga el método save)
        $this->userRepository->save($user, true);

        if ($token) {
            $this->handleInvitation($token, $user);
        }

        // 7️⃣ Devolver DTO de respuesta
        return UserMapper::toResponseDTO($user);
    }

    private function handleInvitation(string $token, User $user): void
    {
        // Lógica para manejar la invitación usando el token
        $invitation = $this->entityManager->getRepository(GroupInvitation::class)->findOneBy(['token' => $token]);

        if($invitation && $invitation->getExpiresAt() > new \DateTimeImmutable()) {
            $group = $invitation->getGroup();
            // 1️⃣ Relación con roles (app_group_member)
            $newMember = new GroupMember($group, $user, 'MEMBER');
            $this->entityManager->persist($newMember);
            // 2️⃣ Relación ManyToMany (user_groups) para el Dashboard
            $user->addGroup($group);
            $group->getMembers()->add($newMember);

            // 3️⃣ Borrar invitación usada
            $this->entityManager->remove($invitation);
            
            // 4️⃣ Guardamos todos los cambios de la invitación y grupo
            $this->entityManager->flush();
        }
            
    }
}
