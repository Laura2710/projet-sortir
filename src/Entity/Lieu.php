<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom doit être renseigné')]
    #[Assert\Length(min: 3, max: 50, minMessage: 'Le nom doit comporter au moins {{ limit }} caractères', maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '^[A-zÀ-ÿ-\' ]{3,}$', message: 'Le nom comporte des caractères interdits')]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La rue doit être renseignée')]
    #[Assert\Length(min: 3, max: 100, minMessage: 'La rue doit comporter au moins {{ limit }} caractères', maxMessage: 'La rue ne doit pas dépasser {{ limit }} caractères')]
    #[Assert\Regex(pattern: '^[0-9A-zÀ-ÿ-\', ]{3,}$', message: 'La rue comporte des caractères interdits')]
    private ?string $rue = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type('float', message: 'La latitude ne respecte pas le format requis')]
    #[Assert\Range(notInRangeMessage: 'La latitude doit être comprise entre -90 et 90', min: -90, max: 90)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type('float', message: 'La longitude ne respecte pas le format requis')]
    #[Assert\Range(notInRangeMessage: 'La longitude doit être comprise entre -180 et 180', min: -180, max: 180)]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'lieus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La ville est obligatoire')]
    private ?Ville $ville = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'lieu', orphanRemoval: true)]
    private Collection $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): static
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties->add($sorty);
            $sorty->setLieu($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): static
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getLieu() === $this) {
                $sorty->setLieu(null);
            }
        }

        return $this;
    }
}
