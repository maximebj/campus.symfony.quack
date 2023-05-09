<?php

namespace App\Controller;

use App\Entity\Quack;
use App\Form\QuackType;
use App\Form\QuackAnswerType;
use App\Repository\QuackRepository;
use App\Services\QuackService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function new(Request $request, QuackService $quackService): Response
    {   
        $quack = new Quack();
        $form = $this->createForm(QuackType::class, $quack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $quackService->handleQuackForm($quack);
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('quack/new.html.twig', [
            'quack' => $quack,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quack_show', methods: ['GET', 'POST'])]
    public function show(Quack $quack, Request $request, QuackRepository $quackRepository, QuackService $quackService): Response
    {
        # Get Quack anwsers
        $quacksAnswers = $quackRepository->findBy(
          ['parent' => $quack->getId()], 
          ['created_at' => 'DESC']
        );
        
        # Get Quack Answer form
        $quackAnswer = new Quack();
        $quackForm = $this->createForm(QuackAnswerType::class, $quackAnswer);
        $quackForm->handleRequest($request);

        if ($quackForm->isSubmitted() && $quackForm->isValid()) {

            # Récupérer le champ démappé (décorellé)
            $parent = $quackForm->get('parent')->getData();
            
            $quackService->handleQuackForm($quackAnswer, $parent); 
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('quack/show.html.twig', [
            'quack' => $quack,
            'quacksAnswers' => $quacksAnswers,
            'form'  => $quackForm,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_quack_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quack $quack, QuackService $quackService): Response
    {
        # Get User
        $user = $this->getUser();

        # Check if user is the owner of the quack
        if ($quack->getUser() !== $user) {
            throw $this->createAccessDeniedException('You are not the owner of this quack');
        }
        
        # Generate form
        $form = $this->createForm(QuackType::class, $quack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $quackService->handleQuackForm($quack);

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
        # Get User
        $user = $this->getUser();
        
        # Check if user is the owner of the quack
        if ($quack->getUser() !== $user) {
            throw $this->createAccessDeniedException('You are not the owner of this quack');
        }
        
        if ($this->isCsrfTokenValid('delete'.$quack->getId(), $request->request->get('_token'))) {
            $quackRepository->remove($quack, true);
        }

        # Delete Quack anwsers
        $quacksAnswers = $quackRepository->findBy(
          ['parent' => $quack->getId()], 
          ['created_at' => 'DESC']
        );

        foreach ($quacksAnswers as $quackAnswer) {
            $quackRepository->remove($quackAnswer, true);
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
