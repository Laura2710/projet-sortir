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

        for ($i = 1; $i <= 10; $i++) {
            $organisateur = $participants[array_rand($participants)];
            $campus = $organisateur->getCampus();
            $lieu = $lieux[array_rand($lieux)];
            $dateDebut = $faker->dateTimeBetween('-2 month', '+1 month');
            $heureDebut = $faker->numberBetween(14,22);
            $dateDebut->setTime($heureDebut, 0);

            $dateCloture = clone $dateDebut;
            $dateCloture->modify('-1 day');

            $sortie = new Sortie();
            $sortie->setOrganisateur($organisateur);
            $sortie->setCampus($campus);
            $sortie->setLieu($lieu);

            $sortie->setNom($faker->word(4));
            $sortie->setDateHeureDebut($dateDebut);
            $sortie->setDateLimiteInscription($dateCloture);
            $sortie->setNbInscriptionsMax(8);
            $sortie->setInfosSortie($faker->paragraph());

            $duree = 60;
            $sortie->setDuree($duree);

            $dateFin = clone $dateDebut;
            $dateFin->modify('+' . $duree . ' minutes');
            $now = new \DateTime();

            $dateDifference = $now->diff($dateFin);

            if ($dateFin < $now && $dateDifference->m >= 1) {
                $sortie->setEtat($manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Activité passée']));
            } elseif ($dateDebut <= $now && $dateFin >= $now) {
                $sortie->setEtat($manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Activité en cours']));
            } elseif ($dateCloture < $now) {
                $sortie->setEtat($manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Cloturee']));
            } else {
                $sortie->setEtat($manager->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 6;
    }
}
