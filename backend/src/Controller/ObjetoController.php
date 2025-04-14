<?php

namespace App\Controller;

use App\Entity\Objeto;
use App\Form\Objeto1Type;
use App\Repository\ObjetoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/objeto')]
final class ObjetoController extends AbstractController
{
    #[Route(name: 'app_objeto_index', methods: ['GET'])]
    public function index(ObjetoRepository $objetoRepository): Response
    {
        return $this->render('objeto/index.html.twig', [
            'objetos' => $objetoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_objeto_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $objeto = new Objeto();
        $form = $this->createForm(Objeto1Type::class, $objeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($objeto);
            $entityManager->flush();

            return $this->redirectToRoute('app_objeto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('objeto/new.html.twig', [
            'objeto' => $objeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id_objeto}', name: 'app_objeto_show', methods: ['GET'])]
    public function show(Objeto $objeto): Response
    {
        return $this->render('objeto/show.html.twig', [
            'objeto' => $objeto,
        ]);
    }

    #[Route('/{id_objeto}/edit', name: 'app_objeto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Objeto $objeto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Objeto1Type::class, $objeto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_objeto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('objeto/edit.html.twig', [
            'objeto' => $objeto,
            'form' => $form,
        ]);
    }

    #[Route('/{id_objeto}', name: 'app_objeto_delete', methods: ['POST'])]
    public function delete(Request $request, Objeto $objeto, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$objeto->getId_objeto(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($objeto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_objeto_index', [], Response::HTTP_SEE_OTHER);
    }
}
