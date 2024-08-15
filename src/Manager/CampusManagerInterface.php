<?php

namespace App\Manager;

interface CampusManagerInterface
{
    public function creerCampus(string $nomCampus) : array;
    public function supprimerCampus(int $idCampus): array;
    public function modifierCampus(int $idCampus, string $nomCampus) : array;
}