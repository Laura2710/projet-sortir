<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $villes = [
            'Nantes' => '44000',
            'Rennes' => '35000',
            'Niort'  => '79000'
        ];

        foreach ($villes as $nom => $codePostal) {
            $ville = new Ville();
            $ville->setNom($nom);
            $ville->setCodePostal($codePostal);

            $manager->persist($ville);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }
}
