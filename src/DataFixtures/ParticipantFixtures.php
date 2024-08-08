<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements OrderedFixtureInterface
{
    public function __construct(UserPasswordHasherInterface $passwordHasher){
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $tousLesCampus = $manager->getRepository(Campus::class)->findAll();

        $faker = Faker\Factory::create('fr_FR');
        $admin = new Participant();
        $admin->setNom($faker->lastName());
        $admin->setPrenom($faker->firstName());
        $admin->setTelephone($faker->phoneNumber());
        $admin->setMail('admin@admin.com');
        $admin->setPseudo('Admin');
        $admin->setCampus($faker->randomElement($tousLesCampus));
        $mdp = 'Pa$$w0rd';
        $hashedPassword = $this->passwordHasher->hashPassword($admin, $mdp);
        $admin->setMotPasse($hashedPassword);
        $admin->setAdministrateur(true);
        $admin->setActif(true);
        $manager->persist($admin);

        for ($i = 0; $i < 5; $i++) {
            $utilisateur = new Participant();
            $utilisateur->setNom($faker->lastName());
            $utilisateur->setPrenom($faker->firstName());
            $utilisateur->setTelephone($faker->phoneNumber());
            $utilisateur->setMail('user'.$i.'@user.com');
            $utilisateur->setPseudo('User'.$i);
            $utilisateur->setCampus($faker->randomElement($tousLesCampus));
            $mdp = 'Pa$$w0rd';
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $mdp);
            $utilisateur->setMotPasse($hashedPassword);
            $utilisateur->setAdministrateur(false);
            $utilisateur->setActif(true);
            $manager->persist($utilisateur);
        }


        $manager->flush();
    }

    public function getOrder()
    {
       return 2;
    }
}
