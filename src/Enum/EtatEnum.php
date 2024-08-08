<?php

namespace App\Enum;

enum EtatEnum: string
{
    case Creee = "Créée";
    case Ouverte = "Ouverte";
    case Cloturee = "Cloturee";
    case EnCours = "Activité en cours";
    case Terminee = "Terminée";
    case Passee = "Activité passée";
    case Annulee = "Annulée";
}
