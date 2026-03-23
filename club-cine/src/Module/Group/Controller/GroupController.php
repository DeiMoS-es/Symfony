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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

    #[Route('/group/{id}/delete', name: 'app_group_delete', methods: ['POST', 'GET'])]
    public function delete(Group $group, GroupService $groupService): Response
    {
        try {
            $groupName = $group->getName();

            // Llamamos al servicio
            $groupService->deleteGroup($group);

            $this->addFlash('success', "El club '$groupName' ha sido disuelto correctamente.");
            return $this->redirectToRoute('user_dashboard');
        } catch (AccessDeniedHttpException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
        }
    }

    /**Endopint para abandonar el grupo */
    #[Route('/{id}/leave', name: 'app_group_leave', methods: ['POST'])]
    public function leave(Group $group, GroupService $groupService): Response
    {
        try {
            $groupName = $group->getName();

            // Delegamos TODA la lógica al servicio (sucesión de owner, borrado de miembro, etc.)
            $groupService->leaveGroup($group);

            $this->addFlash('success', "Has abandonado el club '$groupName' correctamente.");
        } catch (\Exception $e) {
            // Capturamos cualquier error (por ejemplo, si no era miembro)
            $this->addFlash('danger', "No se pudo abandonar el grupo: " . $e->getMessage());
        }

        // Redirigimos al dashboard porque el usuario ya no debería ver la página del grupo
        return $this->redirectToRoute('user_dashboard');
    }

    // #[Route('/{id}/invite', name: 'app_group_invite', methods: ['POST'])]
    // #[IsGranted('ROLE_USER')]
    // public function invite(Request $request, Group $group): Response
    // {
    //     if ($group->getOwner() !== $this->getUser()) {
    //         throw $this->createAccessDeniedException('No tienes permiso.');
    //     }

    //     $form = $this->createForm(GroupInvitationType::class);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $result = $this->groupService->processInvitation($group, $form->get('email')->getData());
    //         [$type, $message] = explode('|', $result);
    //         $this->addFlash($type, $message);
    //     }

    //     return $this->redirectToRoute('app_group_show', ['id' => $group->getId()]);
    // }
}
