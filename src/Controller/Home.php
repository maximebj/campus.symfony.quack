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


class Home extends AbstractController
{
  #[Route('/', name: 'app_home')] 
  public function home(Request $request, QuackRepository $quackRepository, Security $security): Response
  {
    # Get User
    $user = $security->getUser();
    
    # Get Quacks
    $quacks = $quackRepository->findBy([], ['created_at' => 'DESC']);
    
    # Get Quack form
    $quack = new Quack();
    $quackForm = $this->createForm(QuackType::class, $quack);
    $quackForm->handleRequest($request);

    if ($quackForm->isSubmitted() && $quackForm->isValid()) {

      $quack->setCreatedAt(new \DateTime());
      $quack->setUser($user);
      $quackRepository->save($quack, true);
        
      return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('home.html.twig', [
      'quacks' => $quacks,
      'form' => $quackForm,
      'user' => $user,
    ]);
  }
}