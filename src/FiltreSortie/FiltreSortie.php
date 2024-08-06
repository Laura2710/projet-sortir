<?php

namespace App\FiltreSortie;

use App\Entity\Campus;
use Symfony\Component\Validator\Constraints as Assert;

class FiltreSortie
{
    #[Assert\NotNull(message: "Le campus est obligatoire")]
    private ?Campus $campus = null;
    #[Assert\Regex(pattern: '/^[A-zÀ-ú]+$/', message: 'Le nom de la sortie comporte des caractères interdits')]
    private ?string $nomSortie = null;

    private ?\DateTime $dateDebutSortie;

    #[Assert\GreaterThanOrEqual(propertyPath: 'dateDebutSortie', message: "La date de fin doit être supérieur à la date de début")]
    private ?\DateTime $dateFinSortie;
    #[Assert\Type('boolean')]
    private ?bool $estOrganisateur;
    #[Assert\Type('boolean')]
    private ?bool $estInscrit;
    #[Assert\Type('boolean')]
    private ?bool $nonInscrit;
    #[Assert\Type('boolean')]
    private ?bool $sortiesPassees;

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    public function getNomSortie(): ?string
    {
        return $this->nomSortie;
    }

    public function setNomSortie(?string $nomSortie): void
    {
        $this->nomSortie = $nomSortie;
    }

    public function getDateDebutSortie(): ?\DateTime
    {
        return $this->dateDebutSortie;
    }

    public function setDateDebutSortie(?\DateTime $dateDebutSortie): void
    {
        $this->dateDebutSortie = $dateDebutSortie;
    }

    public function getDateFinSortie(): ?\DateTime
    {
        return $this->dateFinSortie;
    }

    public function setDateFinSortie(?\DateTime $dateFinSortie): void
    {
        $this->dateFinSortie = $dateFinSortie;
    }

    public function getEstOrganisateur(): ?bool
    {
        return $this->estOrganisateur;
    }

    public function setEstOrganisateur(?bool $estOrganisateur): void
    {
        $this->estOrganisateur = $estOrganisateur;
    }

    public function getEstInscrit(): ?bool
    {
        return $this->estInscrit;
    }

    public function setEstInscrit(?bool $estInscrit): void
    {
        $this->estInscrit = $estInscrit;
    }

    public function getNonInscrit(): ?bool
    {
        return $this->nonInscrit;
    }

    public function setNonInscrit(?bool $nonInscrit): void
    {
        $this->nonInscrit = $nonInscrit;
    }

    public function getSortiesPassees(): ?bool
    {
        return $this->sortiesPassees;
    }

    public function setSortiesPassees(?bool $sortiesPassees): void
    {
        $this->sortiesPassees = $sortiesPassees;
    }


}