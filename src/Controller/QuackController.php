<?php

namespace App\Controller;

use App\Entity\Quack;
use App\Form\QuackType;
use App\Repository\QuackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/quack')]
class QuackController extends AbstractController
{
    #[Route('/', name: 'app_quack_index', methods: ['GET'])]
    public function index(QuackRepository $quackRepository): Response
    {
        return $this->render('quack/index.html.twig', [
            'quacks' => $quackRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_quack_new', methods: ['GET', 'POST'])]
    public function new(Request $request, QuackRepository $quackRepository, Security $security): Response
    {   
        $quack = new Quack();
        $form = $this->createForm(QuackType::class, $quack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();

            $quack->setCreatedAt(new \DateTime());
            $quack->setUser($user);
            $quackRepository->save($quack, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quack/new.html.twig', [
            'quack' => $quack,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quack_show', methods: ['GET'])]
    public function show(Quack $quack): Response
    {
        return $this->render('quack/show.html.twig', [
            'quack' => $quack,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_quack_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quack $quack, QuackRepository $quackRepository): Response
    {
        $form = $this->createForm(QuackType::class, $quack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quack->setCreatedAt(new \DateTime());
            $quackRepository->save($quack, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quack/edit.html.twig', [
            'quack' => $quack,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_quack_delete', methods: ['POST'])]
    public function delete(Request $request, Quack $quack, QuackRepository $quackRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quack->getId(), $request->request->get('_token'))) {
            $quackRepository->remove($quack, true);
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
