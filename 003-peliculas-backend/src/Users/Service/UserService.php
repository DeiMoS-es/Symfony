<?php

namespace App\Users\Service;
use App\Users\Entity\User;
use App\Users\Entity\Dto\UserInputDTO;
use App\Users\Entity\Dto\UserOutputDTO;
use App\Users\Mapper\UserMapperFromDTO;
use App\Users\Mapper\UserMapperToDTO;
use App\Users\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserMapperToDTO $userMapperToDTO;
    private UserMapperFromDTO $userMapperFromDTO;
    private UserOutputDTO $userOutputDTO;
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserRepository $userRepository, UserMapperToDTO $userMapperToDTO,
                                UserMapperFromDTO $userMapperFromDTO, EntityManagerInterface $em,
                                UserOutputDTO $userOutputDTO, UserPasswordHasherInterface $passwordHasher ) {
        $this->userRepository = $userRepository;
        $this->userMapperToDTO = $userMapperToDTO;
        $this->userMapperFromDTO = $userMapperFromDTO;
        $this->em = $em;
        $this->userOutputDTO = $userOutputDTO;
        $this->passwordHasher = $passwordHasher;
    }

    public function getUserById(int $id): ?UserOutputDTO
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException("Usuario con ID $id no encontrado.");
        }

        return $this->userMapperToDTO->toOutputDTO($user);
    }

    public function createUserFromDto(UserInputDTO $userInputDTO): UserOutputDTO{
        $userExist = $this->userRepository->findOneBy(['email' => $userInputDTO->email]);
        if($userExist){
            throw new \Exception("El usuario con el email {$userInputDTO->email} ya existe.");
        }

        $user = $this->userMapperFromDTO->fromInputDTO($userInputDTO);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $userInputDTO->password);
        $user->setPassword(($hashedPassword));
        $this->userRepository->save($user, true);

        return $this->userMapperToDTO->toOutputDTO($user);
    }

    public function deleteUserById(int $id): bool{
        $user = $this->userRepository->find($id);
        if(!$user){
            return false;
        }
        $user->setStatus(false);
        $this->userRepository->save($user, true);
        return true;

    }
}
