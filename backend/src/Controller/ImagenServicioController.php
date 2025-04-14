<?php

namespace App\Controller;

use App\Entity\ImagenServicio;
use App\Form\ImagenServicio1Type;
use App\Repository\ImagenServicioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/imagen/servicio')]
final class ImagenServicioController extends AbstractController
{
    #[Route(name: 'app_imagen_servicio_index', methods: ['GET'])]
    public function index(ImagenServicioRepository $imagenServicioRepository): Response
    {
        return $this->render('imagen_servicio/index.html.twig', [
            'imagen_servicios' => $imagenServicioRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_imagen_servicio_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $imagenServicio = new ImagenServicio();
        $form = $this->createForm(ImagenServicio1Type::class, $imagenServicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($imagenServicio);
            $entityManager->flush();

            return $this->redirectToRoute('app_imagen_servicio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('imagen_servicio/new.html.twig', [
            'imagen_servicio' => $imagenServicio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_imagen}', name: 'app_imagen_servicio_show', methods: ['GET'])]
    public function show(ImagenServicio $imagenServicio): Response
    {
        return $this->render('imagen_servicio/show.html.twig', [
            'imagen_servicio' => $imagenServicio,
        ]);
    }

    #[Route('/{id_imagen}/edit', name: 'app_imagen_servicio_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ImagenServicio $imagenServicio, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ImagenServicio1Type::class, $imagenServicio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_imagen_servicio_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('imagen_servicio/edit.html.twig', [
            'imagen_servicio' => $imagenServicio,
            'form' => $form,
        ]);
    }

    #[Route('/{id_imagen}', name: 'app_imagen_servicio_delete', methods: ['POST'])]
    public function delete(Request $request, ImagenServicio $imagenServicio, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$imagenServicio->getId_imagen(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($imagenServicio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_imagen_servicio_index', [], Response::HTTP_SEE_OTHER);
    }
}
