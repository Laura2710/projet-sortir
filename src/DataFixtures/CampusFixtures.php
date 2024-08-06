<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 3 campus physiques : Nantes, Rennes, Niort
        $tousLesCampus = ['Nantes', 'Rennes', 'Niort'];

        foreach ($tousLesCampus as $c) {
            $campus = new Campus();
            $campus->setNom($c);
            $manager->persist($campus);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
