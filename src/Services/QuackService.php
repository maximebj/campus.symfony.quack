<?php 

namespace App\Services;

use App\Entity\Quack;
use App\Repository\QuackRepository;
use Symfony\Bundle\SecurityBundle\Security;

class QuackService
{
  protected $quack;

  public function __construct( 
    protected QuackRepository $quackRepository, 
    protected Security $security
  ) {
  }

  public function handleQuackForm(Quack $quack, int|null $parent = null): void
  {
    $this->quack = $quack;
    
    # Récupérer l'utilisateur connecté
    $user = $this->security->getUser();
    
    # Définir la date de publication et l'utilisateur automatiquement
    $this->quack->setCreatedAt(new \DateTime());
    $this->quack->setUser($user);

    # Gestion du parent
    $this->maybeSetParent($parent);
    
    # Enregistrer dans la base de données et flusher
    $this->quackRepository->save($this->quack, true);
  }

  protected function maybeSetParent($parent)
  {
    # Ajout de l'id du parent dans le cas d'un quack de réponse
    if ( $parent === null ) {
      return;
    }

    # On vérifie que le parent n'est pas un enfant d'un autre Quack
    # De cette manière on empêche les réponses de niveau 3
    # l'utilisateur pourrait changer l'id du parent dans le formulaire

    # Récupérer le parent
    $parent = $this->quackRepository->find($parent);

    if( $parent->getParent() !== null ) {
      throw new \Exception("Vous ne pouvez pas répondre à un Quack qui est déjà une réponse");
    }

    # Si tout est bon, on définit le parent
    $this->quack->setParent($parent);
  }
}