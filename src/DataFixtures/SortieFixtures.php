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

        // Use sortie Créée
        $dateCreee = $faker->dateTimeBetween('+5 days', '+1 month');
        $heureDebut = $faker->numberBetween(9, 22);
        $dateCreee->setTime($heureDebut, 0);

        $datesACreer = [$dateDebutSortieOuverte, $dateDebutSortieEnCours, $dateTerminee, $datePassee, $dateCreee];

        foreach ($participants as $participant) {
            for ($i = 0; $i < count($datesACreer); $i++) {
                $sortie = new Sortie();
                $duree = 60;

                $campus = $participant->getCampus();
                $sortie->setOrganisateur($participant);

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

                $choix = ['Créée', 'Ouverte'];
                $sortie->setEtat($manager->getRepository(Etat::class)->findOneBy(['libelle' => $faker->randomElement($choix)]));
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
