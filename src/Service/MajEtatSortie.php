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
        $etats = $this->etatRepository->findAll();

        // Fonction de filtrage pour trouver l'état par libellé
        $findEtatByLibelle = function($libelle) use ($etats) {
            foreach ($etats as $etat) {
                if ($etat->getLibelle() === $libelle) {
                    return $etat;
                }
            }
            throw new \UnexpectedValueException("État non trouvé pour le libellé: $libelle");
        };

        // OUVERTE VERS CLOTURE
        $cloturees = $this->sortieRepository->findACloturee();
        $etatCloturee = $findEtatByLibelle(EtatEnum::Cloturee);
        foreach ($cloturees as $sortie) {
            $sortie->setEtat($etatCloturee);
            $this->entityManager->persist($sortie);
        }

        // CLOTURE VERS EN COURS
        $enCours = $this->sortieRepository->findEnCours();
        $etatEnCours = $findEtatByLibelle(EtatEnum::EnCours);
        foreach ($enCours as $sortie) {
            $sortie->setEtat($etatEnCours);
            $this->entityManager->persist($sortie);
        }

        // EN COURS VERS TERMINEE
        $etatTerminee = $findEtatByLibelle(EtatEnum::Terminee);
        $terminees = $this->sortieRepository->findTerminee();
        foreach ($terminees as $sortie) {
            $sortie->setEtat($etatTerminee);
            $this->entityManager->persist($sortie);
        }

        // TERMINEE VERS PASSEE
        $etatPassee = $findEtatByLibelle(EtatEnum::Passee);
        $passees = $this->sortieRepository->findPassees();
        foreach ($passees as $sortie) {
            $sortie->setEtat($etatPassee);
            $this->entityManager->persist($sortie);
        }

        $this->entityManager->flush();
    }

}