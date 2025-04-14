<?php

namespace App\Controller;

use App\Entity\Servicio;
use App\Form\Servicio1Type;
use App\Repository\ServicioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/servicio')]
final class ServicioController extends AbstractController
{
    #[Route(name: 'app_servicio_index', methods: ['GET'])]
    public function index(ServicioRepository $servicioRepository): Response
    {
        return $this->render('servicio/index.html.twig', [
            'servicios' => $servicioRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_servicio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $servicio = new Servicio();
        $form = $this->createForm(Servicio1Type::class, $servicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($servicio);
            $entityManager->flush();

            return $this->redirectToRoute('app_servicio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('servicio/new.html.twig', [
            'servicio' => $servicio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_servicio}', name: 'app_servicio_show', methods: ['GET'])]
    public function show(Servicio $servicio): Response
    {
        return $this->render('servicio/show.html.twig', [
            'servicio' => $servicio,
        ]);
    }

    #[Route('/{id_servicio}/edit', name: 'app_servicio_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Servicio $servicio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Servicio1Type::class, $servicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_servicio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('servicio/edit.html.twig', [
            'servicio' => $servicio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_servicio}', name: 'app_servicio_delete', methods: ['POST'])]
    public function delete(Request $request, Servicio $servicio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$servicio->getId_servicio(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($servicio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_servicio_index', [], Response::HTTP_SEE_OTHER);
    }
}
