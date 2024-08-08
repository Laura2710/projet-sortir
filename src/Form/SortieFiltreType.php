<?php

namespace App\Form;

use App\Entity\Campus;
use App\FiltreSortie\FiltreSortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'required' => false,
                'label' => 'Campus',
                'placeholder' => 'Tous les campus',
                'mapped' => false,
            ])
            ->add('nomSortie', TextType::class, [
                'label' => 'Le nom de la sortie contient',
                'required' => false,
            ])
            ->add('dateDebutSortie', DateType::class, [
                'label' => 'Entre',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('dateFinSortie', DateType::class, [
                'label' => 'Et',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('estOrganisateur', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur(trice)",
                'required' => false,
                ])
            ->add('estInscrit', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit(e)",
                'required' => false,
            ])
            ->add('nonInscrit', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit(e)",
                'required' => false,
            ])
            ->add('sortiesPassees', CheckboxType::class, [
                'label' => "Sorties passées",
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FiltreSortie::class,
        ]);
    }
}
