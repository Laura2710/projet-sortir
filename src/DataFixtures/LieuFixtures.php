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

        foreach ($lieux as $l) {
            $lieu = new Lieu();
            $lieu->setNom($l);
            $lieu->setVille($faker->randomElement($villes));
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
