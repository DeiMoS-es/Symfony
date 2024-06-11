<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/registration', name: 'user_registration')]
    public function userRegistration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $registration_form = $this->createForm(UserType::class, $user);
        $registration_form->handleRequest($request);
        if ($registration_form->isSubmitted() && $registration_form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $plainTextPassword = $registration_form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainTextPassword);
            $user->setPassword($hashedPassword);
            $this->em->persist($user);
            $this->em->flush();
            return $this->redirectToRoute('user_registration');
        }
        return $this->render('user/index.html.twig', [
            'registration_form' => $registration_form->createView()
        ]);
    }
}
