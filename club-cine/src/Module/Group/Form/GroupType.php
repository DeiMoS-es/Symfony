<?php

namespace App\Module\Group\Form;

use App\Module\Group\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre del Club',
                'attr' => ['placeholder' => 'Ej: Los cinéfilos de barrio'],
                'constraints' => [
                    new NotBlank(['message' => 'El nombre es obligatorio']),
                    new Length(['min' => 3, 'minMessage' => 'Mínimo {{ limit }} caracteres'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => '¿De qué trata este club?']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Vinculamos el formulario directamente a la entidad Group
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}

?>