<?php

namespace App\Service;

class CampusValidator
{
    public function validerNomCampus(string $nomCampus) : bool
    {
        if (empty($nomCampus)) {
            return false;
        }

        if (strlen($nomCampus) < 3) {
            return false;
        }

        if (strlen($nomCampus) > 50) {
            return false;
        }

        if (!preg_match('/^[A-Za-zÀ-ú\s]+$/', $nomCampus)) {
            return false;
        }
        return true;
    }
}