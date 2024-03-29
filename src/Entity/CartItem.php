<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cart $cart = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    private ?Order $parent_order = null;

    #[ORM\Column(nullable: true)]
    private ?bool $in_order = null;

    public function __construct(Product $product = null, int $quantity = null)
    {
        if (!is_null($product))
            $this->product = $product;
        if (!is_null($quantity))
            $this->quantity = $quantity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function increaseQuantity($quantity = 1): self
    {
        $this->quantity += $quantity;
        return $this;
    }

    public function decreaseQuantity($quantity = 1): self
    {
        if ($this->quantity >= $quantity)
            $this->quantity -= $quantity;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function getParentOrder(): ?Order
    {
        return $this->parent_order;
    }

    public function setParentOrder(?Order $parent_order): self
    {
        $this->parent_order = $parent_order;

        return $this;
    }

    public function isInOrder(): ?bool
    {
        return $this->in_order;
    }

    public function setInOrder(?bool $in_order): self
    {
        $this->in_order = $in_order;

        return $this;
    }
}
