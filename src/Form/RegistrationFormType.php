<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère le type (user ou boutique) passé depuis le contrôleur
        $type = $options['user_type'] ?? 'user';

        // Champs communs à tous les utilisateurs
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'required' => true,
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'required' => true,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'required' => true,
            ]);

        // Si c'est une boutique, on ajoute les champs spécifiques
        if ($type === 'boutique') {
            $builder
                ->add('storeName', TextType::class, [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'Nom de la boutique'
                ])
                ->add('storeAddress', TextType::class, [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'Adresse de la boutique'
                ])
                ->add('storePhone', TextType::class, [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'Téléphone'
                ]);
        }

        // Champs communs à la fin
        $builder->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'required' => true,
            'label' => 'J\'accepte les conditions d\'utilisation'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'user_type' => 'user', // Type par défaut
        ]);
    }
}
