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

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $additional_images_links = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $auto_model = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $auto_brand = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(nullable: true)]
    private ?float $length = null;

    #[ORM\Column(nullable: true)]
    private ?float $width = null;

    #[ORM\Column(nullable: true)]
    private ?float $height = null;

    #[ORM\Column(nullable: true)]
    private ?float $weight = null;

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

    public function increaseTotalBalance(int $quantity = 1)
    {
        $this->total_balance += $quantity;
    }

    public function decreaseTotalBalance(int $quantity = 1)
    {
        if ($this->total_balance >= $quantity)
            $this->total_balance -= $quantity;
    }

    public function getAdditionalImagesLinks(): ?string
    {
        return $this->additional_images_links;
    }

    public function setAdditionalImagesLinks(?string $additional_images_links): self
    {
        $this->additional_images_links = $additional_images_links;

        return $this;
    }

    public function getAutoModel(): ?string
    {
        return $this->auto_model;
    }

    public function setAutoModel(?string $auto_model): self
    {
        $this->auto_model = $auto_model;

        return $this;
    }

    public function getAutoBrand(): ?string
    {
        return $this->auto_brand;
    }

    public function setAutoBrand(?string $auto_brand): self
    {
        $this->auto_brand = $auto_brand;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }
}
