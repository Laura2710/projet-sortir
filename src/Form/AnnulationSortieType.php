<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AnnulationSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motifAnnulation', TextAreaType::class, [
                'label' => 'Motif de l\'annulation :',
                'required' => true,
                'attr' => ['rows' => 5],
                'constraints' => [
                    new NotBlank(message: "Le motif d'annulation doit être renseigné"),
                    new Length([
                        'min' => 10,
                        'minMessage' => "Le motif d'annulation doit contenir au moins {{ limit }} caractères"
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
