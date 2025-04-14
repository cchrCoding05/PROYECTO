<?php

namespace App\Controller;

use App\Entity\TransaccionCredito;
use App\Form\TransaccionCredito1Type;
use App\Repository\TransaccionCreditoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaccion/credito')]
final class TransaccionCreditoController extends AbstractController
{
    #[Route(name: 'app_transaccion_credito_index', methods: ['GET'])]
    public function index(TransaccionCreditoRepository $transaccionCreditoRepository): Response
    {
        return $this->render('transaccion_credito/index.html.twig', [
            'transaccion_creditos' => $transaccionCreditoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_transaccion_credito_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transaccionCredito = new TransaccionCredito();
        $form = $this->createForm(TransaccionCredito1Type::class, $transaccionCredito);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($transaccionCredito);
            $entityManager->flush();

            return $this->redirectToRoute('app_transaccion_credito_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transaccion_credito/new.html.twig', [
            'transaccion_credito' => $transaccionCredito,
            'form' => $form,
        ]);
    }

    #[Route('/{id_transaccion}', name: 'app_transaccion_credito_show', methods: ['GET'])]
    public function show(TransaccionCredito $transaccionCredito): Response
    {
        return $this->render('transaccion_credito/show.html.twig', [
            'transaccion_credito' => $transaccionCredito,
        ]);
    }

    #[Route('/{id_transaccion}/edit', name: 'app_transaccion_credito_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TransaccionCredito $transaccionCredito, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TransaccionCredito1Type::class, $transaccionCredito);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_transaccion_credito_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transaccion_credito/edit.html.twig', [
            'transaccion_credito' => $transaccionCredito,
            'form' => $form,
        ]);
    }

    #[Route('/{id_transaccion}', name: 'app_transaccion_credito_delete', methods: ['POST'])]
    public function delete(Request $request, TransaccionCredito $transaccionCredito, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transaccionCredito->getId_transaccion(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($transaccionCredito);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_transaccion_credito_index', [], Response::HTTP_SEE_OTHER);
    }
}
