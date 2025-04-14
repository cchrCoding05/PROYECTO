<?php

namespace App\Controller;

use App\Entity\NegociacionPrecio;
use App\Form\NegociacionPrecio2Type;
use App\Repository\NegociacionPrecioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/negociacion/precio')]
final class NegociacionPrecioController extends AbstractController
{
    #[Route(name: 'app_negociacion_precio_index', methods: ['GET'])]
    public function index(NegociacionPrecioRepository $negociacionPrecioRepository): Response
    {
        return $this->render('negociacion_precio/index.html.twig', [
            'negociacion_precios' => $negociacionPrecioRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_negociacion_precio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $negociacionPrecio = new NegociacionPrecio();
        $form = $this->createForm(NegociacionPrecio2Type::class, $negociacionPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($negociacionPrecio);
            $entityManager->flush();

            return $this->redirectToRoute('app_negociacion_precio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('negociacion_precio/new.html.twig', [
            'negociacion_precio' => $negociacionPrecio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_negociacion}', name: 'app_negociacion_precio_show', methods: ['GET'])]
    public function show(NegociacionPrecio $negociacionPrecio): Response
    {
        return $this->render('negociacion_precio/show.html.twig', [
            'negociacion_precio' => $negociacionPrecio,
        ]);
    }

    #[Route('/{id_negociacion}/edit', name: 'app_negociacion_precio_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NegociacionPrecio $negociacionPrecio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NegociacionPrecio2Type::class, $negociacionPrecio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_negociacion_precio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('negociacion_precio/edit.html.twig', [
            'negociacion_precio' => $negociacionPrecio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_negociacion}', name: 'app_negociacion_precio_delete', methods: ['POST'])]
    public function delete(Request $request, NegociacionPrecio $negociacionPrecio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$negociacionPrecio->getId_negociacion(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($negociacionPrecio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_negociacion_precio_index', [], Response::HTTP_SEE_OTHER);
    }
}
