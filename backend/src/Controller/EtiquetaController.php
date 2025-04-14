<?php

namespace App\Controller;

use App\Entity\Etiqueta;
use App\Form\Etiqueta1Type;
use App\Repository\EtiquetaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etiqueta')]
final class EtiquetaController extends AbstractController
{
    #[Route(name: 'app_etiqueta_index', methods: ['GET'])]
    public function index(EtiquetaRepository $etiquetaRepository): Response
    {
        return $this->render('etiqueta/index.html.twig', [
            'etiquetas' => $etiquetaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_etiqueta_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etiquetum = new Etiqueta();
        $form = $this->createForm(Etiqueta1Type::class, $etiquetum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($etiquetum);
            $entityManager->flush();

            return $this->redirectToRoute('app_etiqueta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('etiqueta/new.html.twig', [
            'etiquetum' => $etiquetum,
            'form' => $form,
        ]);
    }

    #[Route('/{id_etiqueta}', name: 'app_etiqueta_show', methods: ['GET'])]
    public function show(Etiqueta $etiquetum): Response
    {
        return $this->render('etiqueta/show.html.twig', [
            'etiquetum' => $etiquetum,
        ]);
    }

    #[Route('/{id_etiqueta}/edit', name: 'app_etiqueta_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Etiqueta $etiquetum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Etiqueta1Type::class, $etiquetum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_etiqueta_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('etiqueta/edit.html.twig', [
            'etiquetum' => $etiquetum,
            'form' => $form,
        ]);
    }

    #[Route('/{id_etiqueta}', name: 'app_etiqueta_delete', methods: ['POST'])]
    public function delete(Request $request, Etiqueta $etiquetum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$etiquetum->getId_etiqueta(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($etiquetum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_etiqueta_index', [], Response::HTTP_SEE_OTHER);
    }
}
