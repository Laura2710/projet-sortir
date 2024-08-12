<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreerSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'empty_data' => '',
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                //'empty_data' => '',
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                //'empty_data' => '',
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée',
                'attr' => [
                    'int' => true,
                ]
            ])
            ->add('nbInscriptionsMax', NumberType::class, [
                'label' => 'Nombre de places',
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
                'required' => false,
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'disabled' => true,
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nom',
                'mapped' => false,
                'placeholder' => 'Choisissez une ville',
                'attr' => ['class' => 'ville-select'],
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un lieu',
                'attr' => ['class' => 'lieu-select'],
            ])
            ->add('rue', TextType::class, [
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('codePostal', TextType::class, [
                'disabled' => true,
                'mapped' => false,
            ])
            ->add('latitude', TextType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('longitude', TextType::class, [
                'required' => false,
                'mapped' => false,
            ]);

        $formModifierVille = function(FormInterface $form, Ville $ville = null){
            $lieu = (null === $ville) ? [] : $ville->getLieus();
            $codePostal = $ville ? $ville->getCodePostal() : '';

            $form->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choices' => $lieu,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez un lieu',
                'label' => 'Lieu',
                'mapped' => true
            ]);

            $form->add('codePostal', TextType::class, [
                'disabled' => true,
                'mapped' => false,
                'data' => $codePostal,
            ]);
        };

        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifierVille){
                $ville = $event->getForm()->getData();
                $formModifierVille($event->getForm()->getParent(), $ville);
            }
        );

        //
       /* $formModifierLieu = function (FormInterface $form, Lieu $lieu = null) {
            $rue = $lieu ? $lieu->getRue() : '';

            $form->add('rue', TextType::class, [
                'disabled' => true,
                'mapped' => false,
                'data' => $rue,
            ]);
        };

        $builder->get('lieu')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifierLieu) {
                $lieu = $event->getForm()->getData();dump($lieu);
                $formModifierLieu($event->getForm()->getParent(), $lieu);
            }
        );*/
        //
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'lieus' => null,
        ]);
    }
}
