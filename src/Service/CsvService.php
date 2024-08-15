<?php

namespace App\Service;

use App\Manager\ParticipantManager;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvService
{

    public function lire(UploadedFile $fichier) : array
    {
        $csv = Reader::createFromPath($fichier->getRealPath(), 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        $records = $csv->getRecords();

        // Filtrer les lignes vides
        $recordsFiltres = [];
        foreach ($records as $record) {
            if (!empty(array_filter($record))) {
                $recordsFiltres[] = $record;
            }
        }
        return $recordsFiltres;
    }


}