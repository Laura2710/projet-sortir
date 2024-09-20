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
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

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
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir {{ limit }} caractères minimum',
                        // max length allowed by Symfony for security reasons
                        'max' => 50,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial',
                    ])
                ]
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                "label" => "Campus",
            ])
            ->add('avatar',FileType::class, [
                "label" => "Avatar",
                "mapped" => false,
                'required' => false,
                "constraints" => [
                    new Image([
                        'mimeTypesMessage' => "Le format de fichier n'est pas autorisé.",
                        "mimeTypes" => ["image/jpeg","image/png"],
                        'maxSize'=> 500000, // 500ko
                        'maxSizeMessage' => "La taille du fichier est trop volumineuse (max 50ko)",
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
