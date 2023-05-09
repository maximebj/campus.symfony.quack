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


class HomeController extends AbstractController
{
  #[Route('/', name: 'app_home')] 
  public function home(Request $request, QuackRepository $quackRepository, QuackService $quackService): Response
  { 
    # Récupérer les quacks parents uniquement, les plus récents en premier
    $quacks = $quackRepository->findBy(
      ['parent' => null], 
      ['created_at' => 'DESC']
    );
    
    # Générer le formulaire d'ajout de quack
    $quack = new Quack();
    $quackForm = $this->createForm(QuackType::class, $quack);
    $quackForm->handleRequest($request);

    if ($quackForm->isSubmitted() && $quackForm->isValid()) {
      
      # Service qui gère le formulaire et l'enregistrement en BDD
      $quackService->handleQuackForm($quack); 

      # Ajouter un message flash de succès
      $this->addFlash(
          'success',
          'Votre quack a été publié !'
      );

      # Rediriger vers l'accueil après un ajout réussi
      return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    # Affichage du template
    return $this->render('home.html.twig', [
      'quacks' => $quacks,
      'form' => $quackForm
    ]);
  }
}