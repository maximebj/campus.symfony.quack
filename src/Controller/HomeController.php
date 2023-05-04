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
    # Get Quacks
    $quacks = $quackRepository->findBy([], ['created_at' => 'DESC']);
    
    # Get Quack form
    $quack = new Quack();
    $quackForm = $this->createForm(QuackType::class, $quack);
    $quackForm->handleRequest($request);

    if ($quackForm->isSubmitted() && $quackForm->isValid()) {
      
      $quackService->handleQuackForm($quack); 
      return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('home.html.twig', [
      'quacks' => $quacks,
      'form' => $quackForm
    ]);
  }
}