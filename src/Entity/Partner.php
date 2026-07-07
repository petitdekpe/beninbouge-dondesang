<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
#[ORM\Table(name: 'partner')]
class Partner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $logoFilename = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $websiteUrl = null;

    /** Values: 'technique' | 'partenaire' */
    #[ORM\Column(length: 20)]
    private string $category = 'technique';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getLogoFilename(): string { return $this->logoFilename; }
    public function setLogoFilename(string $f): static { $this->logoFilename = $f; return $this; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): static { $this->role = $role; return $this; }

    public function getWebsiteUrl(): ?string { return $this->websiteUrl; }
    public function setWebsiteUrl(?string $url): static { $this->websiteUrl = $url; return $this; }

    public function getCategory(): string { return $this->category; }
    public function setCategory(string $cat): static { $this->category = $cat; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
