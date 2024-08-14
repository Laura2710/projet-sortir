<?php

namespace App\Manager;

use Symfony\Component\Security\Core\User\UserInterface;

interface ParticipantManagerInterface
{
    public function findParticipants(UserInterface $user);
}