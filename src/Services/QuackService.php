<?php 

namespace App\Services;

use App\Entity\Quack;
use App\Form\QuackType;
use App\Repository\QuackRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class QuackService
{
  public function __construct(
    protected Request $request, 
    protected QuackRepository $quackRepository, 
    protected FormBuilderInterface $builder
  ) {}

  public function getQuackForm(Quack|null $quack = null)
  {
    if ($quack === null) {
      $quack = new Quack();
    }
    
    $form = $this->builder->createForm(QuackType::class, $quack);
    $form->handleRequest($this->request);

    if ($form->isSubmitted() && $form->isValid()) {
      $this->quackRepository->save($quack, true);
    }
    var_dump($form);
    return $form;
  }
}