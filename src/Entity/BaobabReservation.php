<?php

namespace App\Entity;

use App\Repository\BaobabReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BaobabReservationRepository::class)]
#[ORM\Table(name: 'baobab_reservation')]
class BaobabReservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $fullName;

    #[ORM\Column(length: 40)]
    private string $phone;

    #[ORM\Column(length: 40)]
    private string $departureCity;

    #[ORM\Column(length: 10)]
    private string $timeSlot;

    #[ORM\Column]
    private int $passengers = 1;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getDepartureCity(): string
    {
        return $this->departureCity;
    }

    public function setDepartureCity(string $departureCity): static
    {
        $this->departureCity = $departureCity;
        return $this;
    }

    public function getTimeSlot(): string
    {
        return $this->timeSlot;
    }

    public function setTimeSlot(string $timeSlot): static
    {
        $this->timeSlot = $timeSlot;
        return $this;
    }

    public function getPassengers(): int
    {
        return $this->passengers;
    }

    public function setPassengers(int $passengers): static
    {
        $this->passengers = $passengers;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
