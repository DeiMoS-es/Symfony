<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Entity\Group;
use App\Module\Group\Form\GroupType;
use App\Module\Group\Form\GroupInvitationType;
use App\Module\Group\Repository\RecommendationRepository;
use App\Module\Group\Services\GroupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/groups')]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly GroupService $groupService
    ) {}

    #[Route('/new', name: 'app_group_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $group = new Group('', $user, null);

        // AÑADIMOS 'action' PARA QUE EL MODAL SEPA A DÓNDE IR
        $form = $this->createForm(GroupType::class, $group, [
            'action' => $this->generateUrl('app_group_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->groupService->createGroup($group, $user);
                $this->addFlash('success', '¡Club fundado!');
                return $this->redirectToRoute('user_dashboard');
            }
            
            // Si hay errores de validación, se renderizarán en el modal
        }

        $isFragment = $request->isXmlHttpRequest() || $request->headers->has('Surrogate-Capability');
        $template = $isFragment ? 'group/_form.html.twig' : 'group/new.html.twig';

        return $this->render($template, ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'app_group_show', methods: ['GET'])]
    public function show(Group $group, RecommendationRepository $recRepo): Response
    {
        return $this->render('group/show.html.twig', [
            'group' => $group,
            'recommendations' => $recRepo->findBy(['group' => $group], ['createdAt' => 'DESC']),
            'invitationForm' => $this->createForm(GroupInvitationType::class)->createView()
        ]);
    }

    #[Route('/{id}/invite', name: 'app_group_invite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function invite(Request $request, Group $group): Response
    {
        if ($group->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tienes permiso.');
        }

        $form = $this->createForm(GroupInvitationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->groupService->processInvitation($group, $form->get('email')->getData());
            [$type, $message] = explode('|', $result);
            $this->addFlash($type, $message);
        }

        return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
    }
}
