<?php

namespace App\Models;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Common\Collections\Collection;

class ParticipantVueDTO
{
    public int $id;
    public string $nom;
    public string $prenom;
    public string $pseudo;
    public Collection $inscriptions;
    public bool $actif;
    public Campus $campus;

    public static function createFromEntity(Participant $participant): ParticipantVueDTO
    {
        $participantVueDTO = new ParticipantVueDTO();
        $participantVueDTO->id = $participant->getId();
        $participantVueDTO->nom = $participant->getNom();
        $participantVueDTO->prenom = $participant->getPrenom();
        $participantVueDTO->pseudo = $participant->getPseudo();
        $participantVueDTO->actif = $participant->isActif();
        $participantVueDTO->campus = $participant->getCampus();
        $participantVueDTO->inscriptions = $participant->getInscriptions();
        return $participantVueDTO;
    }
}