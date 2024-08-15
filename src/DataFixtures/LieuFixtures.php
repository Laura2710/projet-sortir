<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
class LieuFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $lieux = ['Bibliothèque', 'Place du marché', 'Amphithéâtre', 'Bar'];
        $villes = $manager->getRepository(Ville::class)->findAll();
        // Ajout des coordonnées pour les villes
        $villesCoordonnees = [
            'Nantes' => ['latitude' => 47.218371, 'longitude' => -1.553621],
            'Rennes' => ['latitude' => 48.117266, 'longitude' => -1.677793],
            'Niort'  => ['latitude' => 46.32373, 'longitude' => -0.45877],
        ];
        foreach ($lieux as $l) {
            $lieu = new Lieu();
            $lieu->setNom($l);
            $ville = $faker->randomElement($villes);
            $lieu->setVille($ville);

            if (array_key_exists($ville->getNom(), $villesCoordonnees)) {
                $lieu->setLatitude($villesCoordonnees[$ville->getNom()]['latitude']);
                $lieu->setLongitude($villesCoordonnees[$ville->getNom()]['longitude']);
            }

            $lieu->setRue($faker->streetAddress());
            $manager->persist($lieu);
        }


        $manager->flush();
    }

    public function getOrder()
    {
        return 4;
    }
}
