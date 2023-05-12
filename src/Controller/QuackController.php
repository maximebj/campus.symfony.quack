<?php

namespace App\Controller;

use App\Entity\Quack;
use App\Form\QuackType;
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
        # Formulaire d'ajout d'un nouveau Quack
        $quack = new Quack();
        $form = $this->createForm(QuackType::class, $quack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $quackService->handleQuackForm($quack);
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        # Afficher le template
        return $this->render('quack/new.html.twig', [
            'quack' => $quack,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_quack_show', methods: ['GET', 'POST'])]
    public function show(Quack $quack, Request $request, QuackService $quackService, QuackRepository $quackRepository): Response
    {
        # Le quack est récupéré automatiquement grâce au ParamConverter
        # pas besoin de $quack = $quackRepository->find($id);
        
        # Récupérer les enfants de ce Quack
        $quacksAnswers = $quackRepository->getQuacksAnswers($quack);
        
        # Préparer le formulaire de réponse à un Quack
        $quackAnswer = new Quack();
        $quackForm = $this->createForm(QuackType::class, $quackAnswer);
        $quackForm->handleRequest($request);

        if ($quackForm->isSubmitted() && $quackForm->isValid()) {

            # Service de gestion du formulaire de Quack, cette fois on passe le parent en second paramètre
            $quackService->handleQuackForm($quackAnswer, $quack); 

            # Rediriger vers la page du quack
            return $this->redirectToRoute('app_quack_show', ['id' => $quack->getId()], Response::HTTP_SEE_OTHER);
        }
        
        # Afficher le template
        return $this->render('quack/show.html.twig', [
            'quack' => $quack,
            'quacksAnswers' => $quacksAnswers,
            'form' => $quackForm->createView(),    
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'app_quack_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quack $quack, QuackService $quackService): Response
    {
        # Récupérer l'utilisateur connecté
        $user = $this->getUser();

        # On vérifie que l'utilisateur est bien le propriétaire du Quack à modifier
        if ($quack->getUser() !== $user) {
            throw $this->createAccessDeniedException('You are not the owner of this quack');
        }
        
        # Générer le formulaire d'ajout/modification du Quack
        $form = $this->createForm(QuackType::class, $quack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $quackService->handleQuackForm($quack);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        # Afficher le template
        return $this->render('quack/edit.html.twig', [
            'quack' => $quack,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/delete', name: 'app_quack_delete', methods: ['POST'])]
    public function delete(Request $request, Quack $quack, QuackRepository $quackRepository): Response
    {
        # Validation du token CSRF (voir le template)
        if (! $this->isCsrfTokenValid('delete'.$quack->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('You cannot delete this Quack');
        }
        
        # Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        # On vérifie que l'utilisateur est bien le propriétaire du Quack à supprimer
        if ($quack->getUser() !== $user) {

            # On va vérifier qu'on est peut-être le propriétaire du Quack parent du Quack
            # Dans ce cas, on a le droit de supprimer
            if ( $quack->getParentId() !== null ) {

                # On récupère le parent
                $parent = $quackRepository->find($quack->getParentId());

                # On vérifie que l'on est propriétaire du parent
                if( $parent->getUser() !== $user ) {
                    throw $this->createAccessDeniedException('You are not the owner of this quack');
                }

            } else {
                # Si on n'est pas le propriétaire du quack, ni du quack parent, on ne peut pas supprimer
                throw $this->createAccessDeniedException('You are not the owner of this quack');
            }
        }

        # ⚠️ On supprime d'abord les éventuels Quacks enfants 
        # important de le faire en premier

        # On récupère les Quacks enfants
        $quacksAnswers = $quackRepository->getQuacksAnswers($quack);

        # Et on les supprime un par un
        foreach ($quacksAnswers as $quackAnswer) {
            $quackRepository->remove($quackAnswer, true);
        }
        
        # Enfin, on supprime le Quack original
        $quackRepository->remove($quack, true);

        # Ajouter un message flash de validation
        $this->addFlash(
            'error',
            'Votre quack a été supprimé'
        );

        # On redirige vers la page d'accueil
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
