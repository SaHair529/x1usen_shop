<?php

namespace App\Entity;

use App\Repository\AbcpOrderCustomFieldsEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AbcpOrderCustomFieldsEntityRepository::class)]
class AbcpOrderCustomFieldsEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alfabankOrderId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alfabankPaymentUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $abcpOrderNumber = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPaid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlfabankOrderId(): ?string
    {
        return $this->alfabankOrderId;
    }

    public function setAlfabankOrderId(?string $alfabankOrderId): self
    {
        $this->alfabankOrderId = $alfabankOrderId;

        return $this;
    }

    public function getAlfabankPaymentUrl(): ?string
    {
        return $this->alfabankPaymentUrl;
    }

    public function setAlfabankPaymentUrl(?string $alfabankPaymentUrl): self
    {
        $this->alfabankPaymentUrl = $alfabankPaymentUrl;

        return $this;
    }

    public function getAbcpOrderNumber(): ?int
    {
        return $this->abcpOrderNumber;
    }

    public function setAbcpOrderNumber(?int $abcpOrderNumber): self
    {
        $this->abcpOrderNumber = $abcpOrderNumber;

        return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(?bool $isPaid): self
    {
        $this->isPaid = $isPaid;

        return $this;
    }
}
