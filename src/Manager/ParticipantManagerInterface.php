<?php

namespace App\Manager;

use App\Entity\Campus;
use Symfony\Component\Security\Core\User\UserInterface;

interface ParticipantManagerInterface
{
    public function findParticipants(UserInterface $user);
    public function creerParticipant(array $data, Campus $campus): array;
    public function enregistrerParticipantsUpload(array $records) : array;

}