<?php

namespace App\Controller;

use App\Entity\Valoracion;
use App\Form\Valoracion1Type;
use App\Repository\ValoracionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/valoracion')]
final class ValoracionController extends AbstractController
{
    #[Route(name: 'app_valoracion_index', methods: ['GET'])]
    public function index(ValoracionRepository $valoracionRepository): Response
    {
        return $this->render('valoracion/index.html.twig', [
            'valoracions' => $valoracionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_valoracion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $valoracion = new Valoracion();
        $form = $this->createForm(Valoracion1Type::class, $valoracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($valoracion);
            $entityManager->flush();

            return $this->redirectToRoute('app_valoracion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('valoracion/new.html.twig', [
            'valoracion' => $valoracion,
            'form' => $form,
        ]);
    }

    #[Route('/{id_valoracion}', name: 'app_valoracion_show', methods: ['GET'])]
    public function show(Valoracion $valoracion): Response
    {
        return $this->render('valoracion/show.html.twig', [
            'valoracion' => $valoracion,
        ]);
    }

    #[Route('/{id_valoracion}/edit', name: 'app_valoracion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Valoracion $valoracion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Valoracion1Type::class, $valoracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_valoracion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('valoracion/edit.html.twig', [
            'valoracion' => $valoracion,
            'form' => $form,
        ]);
    }

    #[Route('/{id_valoracion}', name: 'app_valoracion_delete', methods: ['POST'])]
    public function delete(Request $request, Valoracion $valoracion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$valoracion->getId_valoracion(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($valoracion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_valoracion_index', [], Response::HTTP_SEE_OTHER);
    }
}
