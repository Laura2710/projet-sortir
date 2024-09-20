<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }


    public function uploadImage(UploadedFile $file, string $directory) : string
    {
        // Récupération du chemin défini dans les paramètres
        $destinationDir = $this->params->get('upload_avatar_profile') . '/' . $directory;

        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $allowedMimeTypes = ['image/jpeg', 'image/png'];
        $maxFileSize = 500000;

        // Creer le dossier si il n'existe pas
        if (!is_dir($destinationDir)) {
            if (!mkdir($destinationDir, 0755, true)) {
                throw new \Exception('Échec de la création du répertoire');
            }
        }

        // Verification de l'extension du fichier
        if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
            throw new \Exception('Extension non autorisée');
        }

        // Verification du type MIME
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('Type MIME non autorisé');
        }

        // Verification de la taille du fichier
        if ($file->getSize() > $maxFileSize) {
            throw new \Exception('La taille du fichier est trop volumineuse (max 500ko)');
        }

        // Création du nom unique pour le fichier
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        // Déplacement du fichier vers le répertoire défini
        $file->move($destinationDir, $fileName);
        return $directory . '/' . $fileName;
    }
}
