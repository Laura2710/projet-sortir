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

        // S'assurer qu'il y a au moins une sortie disponible pour inscription
        $eligibleSorties = array_filter($sorties, function($sortie) {
            return $sortie->getEtat()->getLibelle() != 'Créée'; // Enlever ->value car c'est une chaîne de caractères
        });

        // Répartir les participants
        foreach ($participants as $participant) {
            $inscrit = false;

            // Vérifier les sorties disponibles pour ce participant
            foreach ($eligibleSorties as $sortie) {
                if ($sortie->getOrganisateur() !== $participant) {
                    $sortie->addParticipant($participant);
                    $manager->persist($sortie);
                    $inscrit = true;
                    break; // Arrêter après avoir inscrit le participant à une sortie
                }
            }

            // Si le participant n'est pas inscrit, l'ajouter à la première sortie éligible
            if (!$inscrit && !empty($eligibleSorties)) {
                $firstEligibleSortie = reset($eligibleSorties);
                $firstEligibleSortie->addParticipant($participant);
                $manager->persist($firstEligibleSortie);
            }
        }

        $manager->flush();
    }


    public function getOrder()
    {
        return 7;
    }
}
