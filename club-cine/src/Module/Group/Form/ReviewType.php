<?php

namespace App\Module\Group\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $scores = array_combine(range(1, 10), range(1, 10));

        $builder
            ->add('scoreScript', ChoiceType::class, [
                'label' => 'Guion',
                'choices' => $scores,
                'expanded' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('scoreMainActor', ChoiceType::class, [
                'label' => 'Actor Principal',
                'choices' => $scores,
                'attr' => ['class' => 'form-select']
            ])
            ->add('scoreMainActress', ChoiceType::class, [
                'label' => 'Actriz Principal',
                'choices' => $scores,
                'attr' => ['class' => 'form-select']
            ])
            ->add('scoreSecondaryActors', ChoiceType::class, [
                'label' => 'Reparto Secundario',
                'choices' => $scores,
                'attr' => ['class' => 'form-select']
            ])
            ->add('scoreDirector', ChoiceType::class, [
                'label' => 'Dirección',
                'choices' => $scores,
                'attr' => ['class' => 'form-select']
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Comentario (opcional)',
                'required' => false,
                'attr' => ['rows' => 3, 'maxlength' => 255]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // No vinculamos a la data_class aquí porque el constructor de Review es complejo
            'data_class' => null, 
        ]);
    }
}