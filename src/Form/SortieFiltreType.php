<?php

namespace App\Form;

use App\Entity\Campus;
use App\FiltreSortie\FiltreSortie;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFiltreType extends AbstractType
{

    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'required' => false,
                'label' => 'Campus',
                'placeholder' => 'Tous les campus',
                'mapped' => true,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                },
                'data' => $user->getCampus(),
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
            'method' => 'GET'
        ]);
    }
}
