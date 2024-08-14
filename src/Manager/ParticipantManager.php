<?php

namespace App\Manager;

use App\Entity\Participant;
use App\Manager\ParticipantManagerInterface;
use App\Models\ParticipantVueDTO;
use App\Repository\ParticipantRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipantManager implements ParticipantManagerInterface
{
    public function __construct(private readonly ParticipantRepository $participantRepository)
    {

    }
    public function findParticipants(UserInterface $user): array
    {
        $participants = $this->participantRepository->findParticipants($user);
        return array_map(function (Participant $participant) {
            return ParticipantVueDTO::createFromEntity($participant);
        }, $participants);
    }
}