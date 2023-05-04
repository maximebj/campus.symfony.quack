<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(Request $request, Security $security, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
    {
        # Get User
        $user = $security->getUser();

        # Generate Form
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
            
            $plainPassword = $form->get('password')->getData();
        
            if (! empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $userRepository->save($user, true);
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
