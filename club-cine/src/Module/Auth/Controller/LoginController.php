<?php
namespace App\Module\Auth\Controller;

use App\Module\Auth\Entity\RefreshToken;
use App\Module\Auth\Service\AuthService;
use App\Module\Auth\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/auth/login', name: 'auth_login', methods: ['GET','POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenRepository $refreshRepo,
        AuthService $authService
    ): Response {
        $context = [
            'error' => null,
            'email' => '',
        ];

        if ($request->isMethod('GET')) {
            return $this->render('auth/login.html.twig', $context);
        }

        $email = (string) $request->request->get('email', '');
        $password = (string) $request->request->get('password', '');
        $context['email'] = $email;

        try {
            // Validar credenciales y obtener User
            $user = $authService->loginUser($email, $password);

            // Generar JWT
            $jwt = $jwtManager->create($user);

            // Crear refresh token
            $refreshPlain = bin2hex(random_bytes(64));
            $refreshHash = password_hash($refreshPlain, PASSWORD_DEFAULT);
            $expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P30D'));

            $refreshToken = new RefreshToken($user, $refreshHash, $expiresAt);
            $em->persist($refreshToken);
            $em->flush();

            $cookie = Cookie::create('REFRESH_TOKEN', $refreshPlain, $expiresAt, '/', null, true, true, false, 'Strict');

            $response = $this->render('home.html.twig', ['user' => $user]);
            $response->headers->setCookie($cookie);

            return $response;

        } catch (\App\Module\Auth\Exception\InvalidCredentialsException $e) {
            $context['error'] = $e->getMessage(); // "Credenciales inválidas"
            return $this->render('auth/login.html.twig', $context);
        } catch (\Throwable $e) {
            $context['error'] = 'Error interno del servidor. Intenta más tarde.';
            return $this->render('auth/login.html.twig', $context);
        }
    }
}
