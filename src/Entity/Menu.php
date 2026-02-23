<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'menu')]
#[ORM\HasLifecycleCallbacks]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $prix_par_personne = null;

    #[ORM\Column]
    private ?int $nombre_min_personnes = null;

    #[ORM\Column]
    private ?int $nombre_max_personnes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_creation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $date_modification = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $traiteur = null;

    public function __construct()
    {
        $this->date_creation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrixParPersonne(): ?float
    {
        return $this->prix_par_personne;
    }

    public function setPrixParPersonne(float $prix_par_personne): static
    {
        $this->prix_par_personne = $prix_par_personne;
        return $this;
    }

    public function getNombreMinPersonnes(): ?int
    {
        return $this->nombre_min_personnes;
    }

    public function setNombreMinPersonnes(int $nombre_min_personnes): static
    {
        $this->nombre_min_personnes = $nombre_min_personnes;
        return $this;
    }

    public function getNombreMaxPersonnes(): ?int
    {
        return $this->nombre_max_personnes;
    }

    public function setNombreMaxPersonnes(int $nombre_max_personnes): static
    {
        $this->nombre_max_personnes = $nombre_max_personnes;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->date_creation;
    }

    public function getDateModification(): ?\DateTime
    {
        return $this->date_modification;
    }

    #[ORM\PreUpdate]
    public function updateDateModification(): void
    {
        $this->date_modification = new \DateTime();
    }

    public function getTraiteur(): ?User
    {
        return $this->traiteur;
    }

    public function setTraiteur(?User $traiteur): static
    {
        $this->traiteur = $traiteur;
        return $this;
    }
}
