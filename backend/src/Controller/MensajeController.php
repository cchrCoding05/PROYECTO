<?php

namespace App\Controller;

use App\Entity\Mensaje;
use App\Form\Mensaje1Type;
use App\Repository\MensajeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mensaje')]
final class MensajeController extends AbstractController
{
    #[Route(name: 'app_mensaje_index', methods: ['GET'])]
    public function index(MensajeRepository $mensajeRepository): Response
    {
        return $this->render('mensaje/index.html.twig', [
            'mensajes' => $mensajeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_mensaje_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mensaje = new Mensaje();
        $form = $this->createForm(Mensaje1Type::class, $mensaje);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('app_mensaje_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mensaje/new.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form,
        ]);
    }

    #[Route('/{id_mensaje}', name: 'app_mensaje_show', methods: ['GET'])]
    public function show(Mensaje $mensaje): Response
    {
        return $this->render('mensaje/show.html.twig', [
            'mensaje' => $mensaje,
        ]);
    }

    #[Route('/{id_mensaje}/edit', name: 'app_mensaje_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mensaje $mensaje, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Mensaje1Type::class, $mensaje);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_mensaje_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mensaje/edit.html.twig', [
            'mensaje' => $mensaje,
            'form' => $form,
        ]);
    }

    #[Route('/{id_mensaje}', name: 'app_mensaje_delete', methods: ['POST'])]
    public function delete(Request $request, Mensaje $mensaje, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mensaje->getId_mensaje(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($mensaje);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mensaje_index', [], Response::HTTP_SEE_OTHER);
    }
}
