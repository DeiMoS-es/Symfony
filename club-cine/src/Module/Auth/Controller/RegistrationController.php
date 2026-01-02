<?php

namespace App\Module\Auth\Controller;

use App\Module\Auth\DTO\RegistrationRequest;
use App\Module\Auth\Service\RegistrationService;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Group\Entity\GroupMember;
use App\Module\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/auth')]
class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        RegistrationService $registrationService,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->registrationService = $registrationService;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/register/{token}', name: 'auth_register', methods: ['GET', 'POST'], defaults: ['token' => null])]
    public function register(Request $request, EntityManagerInterface $em, ?string $token = null): Response
    {
        $invitationEmail = '';

        if ($token) {
            $invitation = $em->getRepository(GroupInvitation::class)->findOneBy(['token' => $token]);
            if ($invitation) {
                if ($invitation->getExpiresAt() > new \DateTimeImmutable()) {
                    $invitationEmail = $invitation->getEmail();
                } else {
                    $this->addFlash('warning', 'Esta invitación ha caducado, pero puedes registrarte igualmente.');
                }
            }
        }

        if ($request->isMethod('GET')) {
            return $this->render('auth/register.html.twig', [
                'errors' => [],
                'email' => $invitationEmail,
                'name' => '',
            ]);
        }

        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('auth_register', $csrfToken)) {
            return $this->render('auth/register.html.twig', [
                'errors' => ['Token CSRF inválido.'],
                'email' => $request->request->get('email', ''),
                'name' => $request->request->get('name', ''),
            ]);
        }

        $data = $request->request->all();
        $registrationRequest = new RegistrationRequest();
        $registrationRequest->email = $data['email'] ?? '';
        $registrationRequest->name = $data['name'] ?? '';
        $registrationRequest->plainPassword = $data['password'] ?? '';

        $errors = [];
        foreach ($this->validator->validate($registrationRequest) as $violation) {
            $errors[] = $violation->getMessage();
        }

        if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            $errors[] = 'La contraseña y la confirmación no coinciden.';
        }

        if (count($errors) > 0) {
            return $this->render('auth/register.html.twig', [
                'errors' => $errors,
                'email' => $registrationRequest->email,
                'name' => $registrationRequest->name,
            ]);
        }

        try {
            $this->registrationService->register($registrationRequest, $token);

            $this->addFlash('success', '¡Registro completado!');
            return $this->redirectToRoute('auth_login');
            
        } catch (\Throwable $e) {
            return $this->render('auth/register.html.twig', [
                'errors' => ['Error: ' . $e->getMessage()],
                'email' => $registrationRequest->email,
                'name' => $registrationRequest->name,
            ]);
        }
    }
}
