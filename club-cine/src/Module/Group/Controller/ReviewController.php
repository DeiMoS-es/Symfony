<?php

namespace App\Module\Group\Controller;

use App\Module\Group\Entity\Recommendation;
use App\Module\Group\Form\ReviewType;
use App\Module\Group\Services\ReviewService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReviewController extends AbstractController
{
    public function __construct(
        private readonly ReviewService $reviewService
    ) {}

    #[IsGranted('ROLE_USER')]
    #[Route('/recommendation/{id}/review', name: 'app_review_new')]
    public function new(Recommendation $recommendation, Request $request): Response
    {
        // El formulario no se vincula a la clase porque usamos un constructor rico
        $form = $this->createForm(ReviewType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->reviewService->registerVote(
                    $recommendation,
                    $this->getUser(),
                    $form->getData()
                );

                $this->addFlash('success', '¡Tu puntuación ha sido registrada correctamente!');
                return $this->redirectToRoute('app_group_show', ['id' => $recommendation->getGroup()->getId()]);

            } catch (\LogicException | \InvalidArgumentException $e) {
                // Capturamos las excepciones tanto del Servicio como del Constructor de la Entidad
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app_group_show', ['id' => $recommendation->getGroup()->getId()]);
            }
        }

        return $this->render('group/review/vote.html.twig', [
            'recommendation' => $recommendation,
            'form' => $form->createView(),
        ]);
    }
}