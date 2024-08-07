<?php

namespace App\Service;

use AllowDynamicProperties;
use App\Enum\EtatEnum;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;

#[AllowDynamicProperties]
class MajEtatSortie
{
    public function __construct(EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository)
    {
        $this->entityManager = $entityManager;
        $this->sortieRepository = $sortieRepository;
        $this->etatRepository = $etatRepository;
    }
    public function mettreAjourEtatSortie() {
        $timezone = new \DateTimeZone('Europe/Paris');
        $date = new \DateTime('now', $timezone);
        $sorties = $this->sortieRepository->findByEtats();

        foreach ($sorties as $sortie) {
            $duree = $sortie->getDuree();
            $dateFinSortie = clone $sortie->getDateHeureDebut();
            $dateFinSortie->modify('+' . $duree . ' minutes');
            $datePassee = clone $dateFinSortie;
            $datePassee->modify('+1 month');

            $debut = $sortie->getDateHeureDebut()->format('Y-m-d H:i:s');
            $fin = $dateFinSortie->format('Y-m-d H:i');
            $maintenant = $date->format('Y-m-d H:i');
            $passee = $datePassee->format('Y-m-d H:i');

            // Passer d'ouverte à en cours
            if ($sortie->getEtat()->getLibelle()->value == 'Ouverte') {
                if ($maintenant > $debut && $maintenant < $fin) {
                    $etat = $this->etatRepository->findOneBy(['libelle' => EtatEnum::EnCours]);
                    $sortie->setEtat($etat);
                    $this->entityManager->persist($sortie);
                }
            }

            // Passer d'en cours à clôturer
            if ($sortie->getEtat()->getLibelle()->value == 'Activité en cours') {
                if ($maintenant >= $fin && $fin < $passee) {
                    $etat = $this->etatRepository->findOneBy(['libelle' => EtatEnum::Cloturee]);
                    $sortie->setEtat($etat);
                    $this->entityManager->persist($sortie);
                }
            }


            // Passer de clôturer à activité passée
            if ($sortie->getEtat()->getLibelle()->value == 'Cloturee') {
                if ($maintenant > $fin && $maintenant > $passee) {
                    $etat = $this->etatRepository->findOneBy(['libelle' => EtatEnum::Passee]);
                    $sortie->setEtat($etat);
                    $this->entityManager->persist($sortie);
                }
            }
        }
        $this->entityManager->flush();
    }
}