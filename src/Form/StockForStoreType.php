<?php

namespace App\Form;

use App\Entity\Vinyl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class StockForStoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vinyl', EntityType::class, [
                'class' => Vinyl::class,
                'choice_label' => 'title',
                'placeholder' => 'Sélectionner un vinyle',
            ])
            ->add('quantity', IntegerType::class, ['attr' => ['min' => 0]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // no data_class because we'll map manually to Stock in controller
        ]);
    }
}

