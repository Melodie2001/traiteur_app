<?php

namespace App\Entity;

use App\Repository\QuoteRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRequestRepository::class)]
#[ORM\Table(name: 'quote_request')]
#[ORM\HasLifecycleCallbacks]
class QuoteRequest
{
    public const STATUS_PENDING  = 'PENDING';
    public const STATUS_ACCEPTED = 'ACCEPTED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_CANCELED = 'CANCELED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $traiteur = null;

    #[ORM\ManyToOne(targetEntity: Menu::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Menu $menu = null;

    #[ORM\Column(length: 30)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getClient(): ?User { return $this->client; }
    public function setClient(User $client): static { $this->client = $client; return $this; }

    public function getTraiteur(): ?User { return $this->traiteur; }
    public function setTraiteur(User $traiteur): static { $this->traiteur = $traiteur; return $this; }

    public function getMenu(): ?Menu { return $this->menu; }
    public function setMenu(Menu $menu): static { $this->menu = $menu; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static
    {
        $allowed = [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELED,
        ];

        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Status invalide');
        }

        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}