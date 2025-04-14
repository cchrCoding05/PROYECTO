<?php

namespace App\Controller;

use App\Entity\IntercambioServicio;
use App\Form\IntercambioServicio1Type;
use App\Repository\IntercambioServicioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intercambio/servicio')]
final class IntercambioServicioController extends AbstractController
{
    #[Route(name: 'app_intercambio_servicio_index', methods: ['GET'])]
    public function index(IntercambioServicioRepository $intercambioServicioRepository): Response
    {
        return $this->render('intercambio_servicio/index.html.twig', [
            'intercambio_servicios' => $intercambioServicioRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_intercambio_servicio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $intercambioServicio = new IntercambioServicio();
        $form = $this->createForm(IntercambioServicio1Type::class, $intercambioServicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($intercambioServicio);
            $entityManager->flush();

            return $this->redirectToRoute('app_intercambio_servicio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intercambio_servicio/new.html.twig', [
            'intercambio_servicio' => $intercambioServicio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_intercambio}', name: 'app_intercambio_servicio_show', methods: ['GET'])]
    public function show(IntercambioServicio $intercambioServicio): Response
    {
        return $this->render('intercambio_servicio/show.html.twig', [
            'intercambio_servicio' => $intercambioServicio,
        ]);
    }

    #[Route('/{id_intercambio}/edit', name: 'app_intercambio_servicio_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, IntercambioServicio $intercambioServicio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(IntercambioServicio1Type::class, $intercambioServicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_intercambio_servicio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('intercambio_servicio/edit.html.twig', [
            'intercambio_servicio' => $intercambioServicio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_intercambio}', name: 'app_intercambio_servicio_delete', methods: ['POST'])]
    public function delete(Request $request, IntercambioServicio $intercambioServicio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$intercambioServicio->getId_intercambio(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($intercambioServicio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_intercambio_servicio_index', [], Response::HTTP_SEE_OTHER);
    }
}
