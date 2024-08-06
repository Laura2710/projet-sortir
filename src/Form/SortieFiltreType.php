<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer l'utilisateur connecté
        // $utilisateur = $options['utilisateur'];

        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'required' => true,
                'label' => 'Campus',
                //'data' => $utilisateur->getCampus()
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
                'attr' => [ 'checked' => 'checked' ],
            ])
            ->add('estInscrit', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit(e)",
                'required' => false,
                'attr' => [ 'checked' => 'checked' ],
            ])
            ->add('nonInscrit', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit(e)",
                'required' => false,
                'attr' => [ 'checked' => 'checked' ],
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
            'user' => null
        ]);
    }
}
