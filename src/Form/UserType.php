<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        # On décorelle le mot de passe pour éviter d'afficher son hash dans le formulaire, pour des raisons de sécurité grâce à mapper => false
        $builder
            ->add('first_name', TextType::class, ['required' => true])
            ->add('last_name', TextType::class, ['required' => true])
            ->add('password', PasswordType::class, ['required' => false, 'mapped' => false])
            ->add('email', EmailType::class, ['required' => true])
            ->add('save', SubmitType::class, ['label' => 'Mettre à jour'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}