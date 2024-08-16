<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InscriptionFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $sorties = $manager->getRepository(Sortie::class)->findAll();
        $participants = $manager->getRepository(Participant::class)->findAll();

        // Répartir les participants
        foreach ($participants as $participant) {
            $inscrit = false;

            // Vérifier les sorties disponibles pour ce participant
            foreach ($sorties as $sortie) {
                // Vérifier si le participant n'est pas déjà inscrit à cette sortie
                if ($sortie->getOrganisateur() !== $participant &&
                    $sortie->getEtat()->getLibelle()->value != 'Créée' &&
                    !$sortie->getParticipants()->contains($participant)) {

                    $sortie->addParticipant($participant);
                    $manager->persist($sortie);
                    $inscrit = true;
                    break; // Arrêter après avoir inscrit le participant à une sortie
                }
            }

            // Si le participant n'est pas inscrit, l'ajouter à la première sortie éligible
            if (!$inscrit) {
                foreach ($sorties as $sortie) {
                    if ($sortie->getOrganisateur() !== $participant &&
                        $sortie->getEtat()->getLibelle()->value != 'Créée') {

                        $sortie->addParticipant($participant);
                        $manager->persist($sortie);
                        break;
                    }
                }
            }
        }

        $manager->flush();
    }


    public function getOrder()
    {
        return 7;
    }
}
