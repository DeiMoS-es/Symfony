<?php

namespace App\Module\Group\Controller;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use App\Module\Group\Entity\GroupInvitation;
use App\Module\Group\Entity\GroupMember;
use App\Module\Group\Form\GroupType;
use App\Module\Group\Repository\RecommendationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Module\Group\Form\GroupInvitationType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/groups')]
class GroupController extends AbstractController
{
    #[Route('/new', name: 'app_group_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Debes estar conectado para crear un grupo.');
        }

        $group = new Group('', $user, null);

        // IMPORTANTE: Añadimos 'action' para que el formulario sepa a dónde enviarse
        $form = $this->createForm(GroupType::class, $group, [
            'action' => $this->generateUrl('app_group_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($group);
            $user->addGroup($group);
            $em->flush();

            $this->addFlash('success', '¡Club creado! Bienvenido, administrador.');
            return $this->redirectToRoute('user_dashboard');
        }

        // LÓGICA DE RENDERIZADO:
        // Si la petición viene de un "render(controller...)" o es AJAX, 
        // devolvemos solo el fragmento del formulario.
        if ($request->isXmlHttpRequest() || $request->headers->get('Surrogate-Capability')) {
            return $this->render('group/_form.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // Si no, devolvemos la página completa (por si alguien entra a /groups/new)
        return $this->render('group/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_group_show', methods: ['GET'])]
    public function show(Group $group, RecommendationRepository $recRepo): Response
    {
        $invitationForm = $this->createForm(GroupInvitationType::class);

        // Buscamos las recomendaciones de este grupo, ordenadas por la más reciente
        $recommendations = $recRepo->findBy(
            ['group' => $group],
            ['createdAt' => 'DESC']
        );

        return $this->render('group/show.html.twig', [
            'group' => $group,
            'recommendations' => $recommendations, // <--- Pasamos las recomendaciones a la vista
            'invitationForm' => $invitationForm->createView()
        ]);
    }

    #[Route('/{id}/invite', name: 'app_group_invite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function invite(Request $request, Group $group, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // 1. Seguridad: Solo el OWNER puede invitar
        // Forzamos al editor a entender que ambos son objetos User para la comparación
        if ($group->getOwner() instanceof User && $currentUser instanceof User) {
            if ($group->getOwner()->getId() !== $currentUser->getId()) {
                throw $this->createAccessDeniedException('Solo el creador del club puede enviar invitaciones.');
            }
        }

        $form = $this->createForm(GroupInvitationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            $userRepo = $em->getRepository(User::class);
            /** @var User|null $existingUser */
            $existingUser = $userRepo->findOneBy(['email' => $email]);

            if ($existingUser instanceof User) {
                // Comprobar si ya es miembro
                $isAlreadyMember = $group->getMembers()->exists(function ($key, $member) use ($existingUser) {
                    // Si el editor falla aquí, usamos una comparación manual de IDs que es infalible
                    if (!$member instanceof GroupMember) return false;
                    return $member->getUser()->getId() === $existingUser->getId();
                });

                if ($isAlreadyMember) {
                    $this->addFlash('warning', 'Esta persona ya es miembro del club.');
                } else {
                    $newMember = new GroupMember($group, $existingUser, 'MEMBER');
                    $em->persist($newMember);
                    $this->addFlash('success', "{$existingUser->getName()} ha sido añadido directamente al club.");
                }
            } else {
                // 2. CREAMOS LA INVITACIÓN
                $expiresAt = new \DateTimeImmutable('+7 days');
                $invitation = new GroupInvitation($email, $group, $expiresAt);

                $em->persist($invitation);
                // 3. ENVIAMOS EL CORREO
                $emailMessage = (new TemplatedEmail())
                    ->from('clubdecine@tuapp.com')
                    ->to($email)
                    ->subject("Te han invitado al club: " . $group->getName())
                    ->htmlTemplate('emails/invitation.html.twig')
                    ->context([
                        'groupName' => $group->getName(),
                        'token' => $invitation->getToken(),
                    ]);

                $mailer->send($emailMessage);
                $this->addFlash('success', "Se ha enviado una invitación a $email. Caduca en 7 días.");
            }

            $em->flush();
        }

        return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
    }
}
