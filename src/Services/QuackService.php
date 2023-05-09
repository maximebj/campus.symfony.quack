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
    # Récupérer l'utilisateur connecté
    $user = $this->security->getUser();
    
    # Définir la date de publication et l'utilisateur automatiquement
    $quack->setCreatedAt(new \DateTime());
    $quack->setUser($user);

    # Ajout de l'id du parent dans le cas d'un quack de réponse
    if ( $parent !== null ) {
      $quack->setParent($parent);
    }

    # Enregistrer dans la base de données et flusher
    $this->quackRepository->save($quack, true);
  }
}