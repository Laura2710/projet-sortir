<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $lieux = $manager->getRepository(Lieu::class)->findAll();
        $participants = $manager->getRepository(Participant::class)->findAll();
        $faker = Factory::create('fr_FR');
        $nomsSortie = ['Randonnée', 'Pique-Nique', 'Exposition', 'Cinema', 'Atelier cuisine', 'Apero'];

        // Une sortie ouverte
        $dateDebutSortieOuverte = $faker->dateTimeBetween('+1 day', '+1 month');
        $heureDebut = $faker->numberBetween(9, 22);
        $dateDebutSortieOuverte->setTime($heureDebut, 0);

        // Une sortie en cours
        $dateDebutSortieEnCours = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $dateDebutSortieEnCours->modify('-50 minutes');

        // Une sortie Terminée
        $dateTerminee = new \DateTime();
        $dateTerminee->modify('-1 day');

        // Une sortie Passée
        $datePassee = new \DateTime();
        $datePassee->modify('-1 month');

        $datesACreer = [$dateDebutSortieOuverte, $dateDebutSortieEnCours, $dateTerminee, $datePassee];

     for ($j = 0; $j < 15; $j++) {
         for ($i = 0; $i < count($datesACreer); $i++) {
             $sortie = new Sortie();
             $duree = 60;
             $organisateur = $faker->randomElement($participants);
             $campus = $organisateur->getCampus();
             $sortie->setOrganisateur($organisateur);
             $sortie->setCampus($campus);
             $sortie->setLieu($faker->randomElement($lieux));
             $sortie->setNom($faker->randomElement($nomsSortie));
             $sortie->setDuree($duree);

             $randomDate = $datesACreer[$i];
             $sortie->setDateHeureDebut($randomDate);
             $dateCloture = clone $randomDate;
             $dateCloture->modify('-1 day');
             $sortie->setDateLimiteInscription($dateCloture);

             $sortie->setNbInscriptionsMax(8);
             $sortie->setInfosSortie($faker->paragraph());

             $sortie->setEtat($manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
             $manager->persist($sortie);
         }
     }

        $manager->flush();
    }

    public function getOrder()
    {
        return 6;
    }
}
