<?php 

namespace App\Services;

use App\Entity\Quack;
use App\Repository\QuackRepository;
use Symfony\Bundle\SecurityBundle\Security;

class QuackService
{
  public function __construct( 
    protected QuackRepository $quackRepository, 
    protected Security $security
  ) {}

  public function handleQuackForm(Quack $quack, int|null $parent = null): void
  {
    # Get User
    $user = $this->security->getUser();
    
    # Set current date and current user
    $quack->setCreatedAt(new \DateTime());
    $quack->setUser($user);

    # Set Parent if is answer
    if ( $parent !== null ) {
      $quack->setParent($parent);
    }

    # Save in DB + Flush
    $this->quackRepository->save($quack, true);
  }
}