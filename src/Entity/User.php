<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'owner', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cart $cart = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\Column]
    private ?int $client_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $organization_name = null;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: OrderComment::class, orphanRemoval: true)]
    private Collection $orderComments;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 50)]
    private ?string $abcp_user_code = null;

    #[ORM\Column(length: 255)]
    private ?string $password_md5 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $bankName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankBik = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $inn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $correspondentAccount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $checkingAccount = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $organisationType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $juridicalAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $juridicalEntityType = null;

    public function __toString(): string
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->orderComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setCustomer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
            }
        }

        return $this;
    }

    public function getClientType(): ?int
    {
        return $this->client_type;
    }

    public function setClientType(int $client_type): self
    {
        $this->client_type = $client_type;

        return $this;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organization_name;
    }

    public function setOrganizationName(?string $organization_name): self
    {
        $this->organization_name = $organization_name;

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
            $notification->setRecipient($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getRecipient() === $this) {
                $notification->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderComment>
     */
    public function getOrderComments(): Collection
    {
        return $this->orderComments;
    }

    public function addOrderComment(OrderComment $orderComment): self
    {
        if (!$this->orderComments->contains($orderComment)) {
            $this->orderComments->add($orderComment);
            $orderComment->setSender($this);
        }

        return $this;
    }

    public function removeOrderComment(OrderComment $orderComment): self
    {
        if ($this->orderComments->removeElement($orderComment)) {
            // set the owning side to null (unless already changed)
            if ($orderComment->getSender() === $this) {
                $orderComment->setSender(null);
            }
        }

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAbcpUserCode(): ?string
    {
        return $this->abcp_user_code;
    }

    public function setAbcpUserCode(string $abcp_user_code): self
    {
        $this->abcp_user_code = $abcp_user_code;

        return $this;
    }

    public function getPasswordMd5(): ?string
    {
        return $this->password_md5;
    }

    public function setPasswordMd5(string $password_md5): self
    {
        $this->password_md5 = $password_md5;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBankBik(): ?string
    {
        return $this->bankBik;
    }

    public function setBankBik(?string $bankBik): self
    {
        $this->bankBik = $bankBik;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

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

    public function getInn(): ?string
    {
        return $this->inn;
    }

    public function setInn(?string $inn): self
    {
        $this->inn = $inn;

        return $this;
    }

    public function getCorrespondentAccount(): ?string
    {
        return $this->correspondentAccount;
    }

    public function setCorrespondentAccount(?string $correspondentAccount): self
    {
        $this->correspondentAccount = $correspondentAccount;

        return $this;
    }

    public function getCheckingAccount(): ?string
    {
        return $this->checkingAccount;
    }

    public function setCheckingAccount(?string $checkingAccount): self
    {
        $this->checkingAccount = $checkingAccount;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getOrganisationType(): ?string
    {
        return $this->organisationType;
    }

    public function setOrganisationType(?string $organisationType): self
    {
        $this->organisationType = $organisationType;

        return $this;
    }

    public function getJuridicalAddress(): ?string
    {
        return $this->juridicalAddress;
    }

    public function setJuridicalAddress(?string $juridicalAddress): self
    {
        $this->juridicalAddress = $juridicalAddress;

        return $this;
    }

    public function getJuridicalEntityType(): ?string
    {
        return $this->juridicalEntityType;
    }

    public function setJuridicalEntityType(?string $juridicalEntityType): self
    {
        $this->juridicalEntityType = $juridicalEntityType;

        return $this;
    }
}
