<?php

namespace App\Entity;

use App\Repository\DellinPlaceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DellinPlaceRepository::class)]
class DellinPlace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $city_id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $search_string = null;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    private ?string $region_code = null;

    #[ORM\Column(length: 255)]
    private ?string $zone_name = null;

    #[ORM\Column(length: 255)]
    private ?string $zone_code = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCityId(): ?int
    {
        return $this->city_id;
    }

    public function setCityId(int $city_id): self
    {
        $this->city_id = $city_id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getSearchString(): ?string
    {
        return $this->search_string;
    }

    public function setSearchString(string $search_string): self
    {
        $this->search_string = $search_string;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getRegionCode(): ?string
    {
        return $this->region_code;
    }

    public function setRegionCode(string $region_code): self
    {
        $this->region_code = $region_code;

        return $this;
    }

    public function getZoneName(): ?string
    {
        return $this->zone_name;
    }

    public function setZoneName(string $zone_name): self
    {
        $this->zone_name = $zone_name;

        return $this;
    }

    public function getZoneCode(): ?string
    {
        return $this->zone_code;
    }

    public function setZoneCode(string $zone_code): self
    {
        $this->zone_code = $zone_code;

        return $this;
    }
}
