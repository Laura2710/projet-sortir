<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Enum\EtatEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $etatCreee = new Etat();
        $etatCreee->setLibelle(EtatEnum::Creee);
        $manager->persist($etatCreee);

        $etatCloturee = new Etat();
        $etatCloturee->setLibelle(EtatEnum::Cloturee);
        $manager->persist($etatCloturee);

        $etatAnnulee = new Etat();
        $etatAnnulee->setLibelle(EtatEnum::Annulee);
        $manager->persist($etatAnnulee);

        $etatEnCours = new Etat();
        $etatEnCours->setLibelle(EtatEnum::EnCours);
        $manager->persist($etatEnCours);

        $etatOuverte = new Etat();
        $etatOuverte->setLibelle(EtatEnum::Ouverte);
        $manager->persist($etatOuverte);

        $etatPassee = new Etat();
        $etatPassee->setLibelle(EtatEnum::Passee);
        $manager->persist($etatPassee);

        $etatTerminee = new Etat();
        $etatTerminee->setLibelle(EtatEnum::Terminee);
        $manager->persist($etatTerminee);


        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
