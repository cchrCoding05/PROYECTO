<?php

namespace App\Controller;

use App\Entity\ImagenObjeto;
use App\Form\ImagenObjeto1Type;
use App\Repository\ImagenObjetoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/imagen/objeto')]
final class ImagenObjetoController extends AbstractController
{
    #[Route(name: 'app_imagen_objeto_index', methods: ['GET'])]
    public function index(ImagenObjetoRepository $imagenObjetoRepository): Response
    {
        return $this->render('imagen_objeto/index.html.twig', [
            'imagen_objetos' => $imagenObjetoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_imagen_objeto_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $imagenObjeto = new ImagenObjeto();
        $form = $this->createForm(ImagenObjeto1Type::class, $imagenObjeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($imagenObjeto);
            $entityManager->flush();

            return $this->redirectToRoute('app_imagen_objeto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('imagen_objeto/new.html.twig', [
            'imagen_objeto' => $imagenObjeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id_imagen}', name: 'app_imagen_objeto_show', methods: ['GET'])]
    public function show(ImagenObjeto $imagenObjeto): Response
    {
        return $this->render('imagen_objeto/show.html.twig', [
            'imagen_objeto' => $imagenObjeto,
        ]);
    }

    #[Route('/{id_imagen}/edit', name: 'app_imagen_objeto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ImagenObjeto $imagenObjeto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ImagenObjeto1Type::class, $imagenObjeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_imagen_objeto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('imagen_objeto/edit.html.twig', [
            'imagen_objeto' => $imagenObjeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id_imagen}', name: 'app_imagen_objeto_delete', methods: ['POST'])]
    public function delete(Request $request, ImagenObjeto $imagenObjeto, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$imagenObjeto->getId_imagen(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($imagenObjeto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_imagen_objeto_index', [], Response::HTTP_SEE_OTHER);
    }
}
