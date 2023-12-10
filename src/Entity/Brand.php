<?php

namespace App\Entity;

use App\Repository\BrandRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BrandRepository::class)]
class Brand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    private ?string $article_number = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    public function __construct(string $brand = null, string $article_number = null, string $model = null)
    {
        if ($brand !== null
            && $article_number !== null
            && $model !== null) {

            $this->brand = $brand;
            $this->article_number = $article_number;
            $this->model = $model;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getArticleNumber(): ?string
    {
        return $this->article_number;
    }

    public function setArticleNumber(string $article_number): self
    {
        $this->article_number = $article_number;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }
}
