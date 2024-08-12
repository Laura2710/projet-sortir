<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('pseudo',TextType::class,[
                "required" =>true,
                "label" => "Pseudo",
            ])
            ->add('prenom',TextType::class,[
                "required" =>true,
                "label" => "Prenom",
            ])
            ->add('nom',TextType::class,[
                "required" =>true,
                "label" => "Nom",
            ])
            ->add('telephone',TextType::class,[
                "label" => "Téléphone"
            ])
            ->add('mail',EmailType::class, [
                "required" =>true,
                "label" => "Email",
            ])
            ->add('motDePasse',RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => "Les mots de passe ne correspondent pas",
                "required" =>false,
                'mapped' => false,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation Mot de passe'],
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                "label" => "Campus",
            ])
//            ->add('avatar',TextType::class, [
//                "label" => "Avatar"
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
