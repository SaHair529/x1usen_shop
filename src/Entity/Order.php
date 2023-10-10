<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\OneToMany(mappedBy: 'parent_order', targetEntity: CartItem::class)]
    private Collection $items;

    #[ORM\Column(length: 20)]
    private ?string $phone_number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $customer = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?int $payment_type = null;

    #[ORM\Column(length: 50)]
    private ?int $status = null;

    #[ORM\Column(length: 255)]
    private ?string $client_fullname = null;

    #[ORM\Column(length: 55)]
    private ?int $way_to_get = null;

    #[ORM\OneToMany(mappedBy: 'updated_order', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'parentOrder', targetEntity: OrderComment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?int $delivery_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alfabank_order_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alfabank_payment_url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address_geocoords = null;

    #[ORM\Column]
    private ?int $payment_status = null;

    #[Pure]
    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CartItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setParentOrder($this);
        }

        return $this;
    }

    public function removeItem(CartItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getParentOrder() === $this) {
                $item->setParentOrder(null);
            }
        }

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): self
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getPaymentType(): ?int
    {
        return $this->payment_type;
    }

    public function setPaymentType(int $payment_type): self
    {
        $this->payment_type = $payment_type;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getClientFullname(): ?string
    {
        return $this->client_fullname;
    }

    public function setClientFullname(string $client_fullname): self
    {
        $this->client_fullname = $client_fullname;

        return $this;
    }

    public function getWayToGet(): ?int
    {
        return $this->way_to_get;
    }

    public function setWayToGet(int $way_to_get): self
    {
        $this->way_to_get = $way_to_get;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUpdatedOrder($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUpdatedOrder() === $this) {
                $notification->setUpdatedOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(OrderComment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setParentOrder($this);
        }

        return $this;
    }

    public function removeComment(OrderComment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getParentOrder() === $this) {
                $comment->setParentOrder(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDeliveryType(): ?int
    {
        return $this->delivery_type;
    }

    public function setDeliveryType(?int $delivery_type): self
    {
        $this->delivery_type = $delivery_type;

        return $this;
    }

    public function getAlfabankOrderId(): ?string
    {
        return $this->alfabank_order_id;
    }

    public function setAlfabankOrderId(?string $alfabank_order_id): self
    {
        $this->alfabank_order_id = $alfabank_order_id;

        return $this;
    }

    public function getAlfabankPaymentUrl(): ?string
    {
        return $this->alfabank_payment_url;
    }

    public function setAlfabankPaymentUrl(?string $alfabank_payment_url): self
    {
        $this->alfabank_payment_url = $alfabank_payment_url;

        return $this;
    }

    public function getAddressGeocoords(): ?string
    {
        return $this->address_geocoords;
    }

    public function setAddressGeocoords(?string $address_geocoords): self
    {
        $this->address_geocoords = $address_geocoords;

        return $this;
    }

    public function getPaymentStatus(): ?int
    {
        return $this->payment_status;
    }

    public function setPaymentStatus(int $payment_status): self
    {
        $this->payment_status = $payment_status;

        return $this;
    }
}
