<?php

namespace App\Users\Service;

use App\Users\Entity\Dto\UserOutputDTO;
use App\Users\Mapper\UserMapperFromDTO;
use App\Users\Mapper\UserMapperToDTO;
use App\Users\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    private UserRepository $userRepository;
    private UserMapperToDTO $userMapperToDTO;
    private UserMapperFromDTO $userMapperFromDTO;
    private UserOutputDTO $userOutputDTO;
    private EntityManagerInterface $em;

    public function __construct(UserRepository $userRepository, UserMapperToDTO $userMapperToDTO,
                                UserMapperFromDTO $userMapperFromDTO, EntityManagerInterface $em,
                                UserOutputDTO $userOutputDTO ) {
        $this->userRepository = $userRepository;
        $this->userMapperToDTO = $userMapperToDTO;
        $this->userMapperFromDTO = $userMapperFromDTO;
        $this->em = $em;
        $this->userOutputDTO = $userOutputDTO;
    }

    public function getUserById(int $id): ?UserOutputDTO
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException("Usuario con ID $id no encontrado.");
        }

        return $this->userMapperToDTO->toOutputDTO($user);
    }
}
