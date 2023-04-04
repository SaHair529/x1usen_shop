<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $article_number = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?float $total_balance = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $measurement_unit = null;

    #[ORM\Column(nullable: true)]
    private ?float $additional_price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_link = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $technical_description = null;

    #[ORM\Column(nullable: true)]
    private ?int $used = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getArticleNumber(): ?string
    {
        return $this->article_number;
    }

    public function setArticleNumber(?string $article_number): self
    {
        $this->article_number = $article_number;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTotalBalance(): ?float
    {
        return $this->total_balance;
    }

    public function setTotalBalance(?float $total_balance): self
    {
        $this->total_balance = $total_balance;

        return $this;
    }

    public function getMeasurementUnit(): ?string
    {
        return $this->measurement_unit;
    }

    public function setMeasurementUnit(?string $measurement_unit): self
    {
        $this->measurement_unit = $measurement_unit;

        return $this;
    }

    public function getAdditionalPrice(): ?float
    {
        return $this->additional_price;
    }

    public function setAdditionalPrice(?float $additional_price): self
    {
        $this->additional_price = $additional_price;

        return $this;
    }

    public function getImageLink(): ?string
    {
        return $this->image_link;
    }

    public function setImageLink(?string $image_link): self
    {
        $this->image_link = $image_link;

        return $this;
    }

    public function getTechnicalDescription(): ?string
    {
        return $this->technical_description;
    }

    public function setTechnicalDescription(?string $technical_description): self
    {
        $this->technical_description = $technical_description;

        return $this;
    }

    public function getUsed(): ?int
    {
        return $this->used;
    }

    public function setUsed(?int $used): self
    {
        $this->used = $used;

        return $this;
    }

    public function incrementTotalBalance(int $quantity = 1)
    {
        $this->total_balance += $quantity;
    }

    public function decrementTotalBalance(int $quantity = 1)
    {
        if ($this->total_balance !== 0) {
            $this->total_balance -= $quantity;
        }
    }
}
