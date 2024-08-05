<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'setting', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $dateFormat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $payment = null;

    #[ORM\Column]
    private ?int $delayDays = null;

    #[ORM\Column]
    private ?bool $installmentPayments = null;

    #[ORM\Column]
    private ?bool $freeMaintenance = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $interfaceLangage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(string $dateFormat): static
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getPayment(): ?string
    {
        return $this->payment;
    }

    public function setPayment(?string $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getDelayDays(): ?int
    {
        return $this->delayDays;
    }

    public function setDelayDays(int $delayDays): static
    {
        $this->delayDays = $delayDays;

        return $this;
    }

    public function isInstallmentPayments(): ?bool
    {
        return $this->installmentPayments;
    }

    public function setInstallmentPayments(bool $installmentPayments): static
    {
        $this->installmentPayments = $installmentPayments;

        return $this;
    }

    public function isFreeMaintenance(): ?bool
    {
        return $this->freeMaintenance;
    }

    public function setFreeMaintenance(bool $freeMaintenance): static
    {
        $this->freeMaintenance = $freeMaintenance;

        return $this;
    }

    public function getInterfaceLangage(): ?string
    {
        return $this->interfaceLangage;
    }

    public function setInterfaceLangage(string $interfaceLangage): static
    {
        $this->interfaceLangage = $interfaceLangage;

        return $this;
    }
}
