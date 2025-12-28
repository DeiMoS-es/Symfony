<?php

namespace App\Module\Group\Controller;

use App\Module\Auth\Entity\User;
use App\Module\Group\Entity\Group;
use App\Module\Group\Form\GroupType;
use App\Module\Group\Repository\RecommendationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        // Buscamos las recomendaciones de este grupo, ordenadas por la más reciente
        $recommendations = $recRepo->findBy(
            ['group' => $group],
            ['createdAt' => 'DESC']
        );

        return $this->render('group/show.html.twig', [
            'group' => $group,
            'recommendations' => $recommendations, // <--- Pasamos las recomendaciones a la vista
        ]);
    }
}
