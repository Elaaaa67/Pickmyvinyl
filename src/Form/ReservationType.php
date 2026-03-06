<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Vinyl;
use App\Entity\Store;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vinyl', EntityType::class, [
                'class' => Vinyl::class,
                'choice_label' => 'title',
                'label' => 'Vinyle'
            ])
            ->add('store', EntityType::class, [
                'class' => Store::class,
                'choice_label' => 'name',
                'label' => 'Enseigne'
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['min' => 1, 'max' => 10],
                'required' => true
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'required' => false,
                'data' => 'pending'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}

