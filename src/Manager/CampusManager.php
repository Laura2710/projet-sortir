<?php

namespace App\Manager;

use App\Entity\Campus;
use App\Repository\CampusRepository;
use App\Service\CampusValidator;
use Doctrine\ORM\EntityManagerInterface;

readonly class CampusManager implements CampusManagerInterface
{
    public function __construct(
        private CampusRepository       $campusRepository,
        private EntityManagerInterface $entityManager,
        private CampusValidator        $campusValidator)
    {
    }
    public function creerCampus(string $nomCampus) : array
    {
        if (!$this->campusValidator->validerNomCampus($nomCampus)) {
            return ['status' => 'error', 'message' => 'Le nom de campus est incorrect'];
        }

        $campusExistant = $this->campusRepository->findOneBy(['nom' => $nomCampus]);
        if ($campusExistant) {
            return ['status' => 'error', 'message' => 'Le campus existe déjà'];
        }

        $campus = new Campus();
        $campus->setNom($nomCampus);
        $this->entityManager->persist($campus);
        $this->entityManager->flush();
        return ['status' => 'ok'];
    }

    public function supprimerCampus(int $idCampus): array
    {
        $campus = $this->campusRepository->findOneBy(['id' => $idCampus]);
        if (!$campus) {
            return ['status' => 'error', 'message' => 'Campus introuvable'];
        }
        $participants = $campus->getParticipants();
        $sorties = $campus->getSorties();
        if (count($participants) > 0 || count($sorties) > 0) {
            return ['status' => 'error', 'message' => 'Le campus ne peut pas être supprimé'];
        }

        $this->entityManager->remove($campus);
        $this->entityManager->flush();

        return ['status' => 'success', 'message' => 'Campus supprimé avec succès'];
    }

    public function modifierCampus(int $idCampus, string $nomCampus) : array
    {
        if (!$this->campusValidator->validerNomCampus($nomCampus)) {
            return ['status' => 'error', 'message' => 'Le nom de campus est incorrect'];
        }
        $campus = $this->campusRepository->find($idCampus);
        if (!$campus) {
            return ['status' => 'error', 'message' => 'Campus introuvable'];
        }

        $campus->setNom($nomCampus);
        $this->entityManager->flush();

        return ['status' => 'success', 'message' => 'Campus modifié avec succès'];
    }
}