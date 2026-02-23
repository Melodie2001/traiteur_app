<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $note = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_creation = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

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

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        if ($note < 1 || $note > 5) {
            throw new \InvalidArgumentException('La note doit être entre 1 et 5.');
        }

        $this->note = $note;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->date_creation;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;
        return $this;
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