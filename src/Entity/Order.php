<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_DELIVERED = 'Delivered';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('read')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('read')]
    private UserInterface $user;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups('read')]
    private DateTimeImmutable $expectedDelivery;

    #[ORM\ManyToOne(targetEntity: Address::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('read')]
    private Address $deliveryAddress;

    #[ORM\ManyToOne(targetEntity: Address::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('read')]
    private Address $billingAddress;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: OrderItem::class, orphanRemoval: true)]
    #[Groups('read')]
    private Collection $orderItems;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('read')]
    private string $status;

    #[ORM\OneToMany(mappedBy: 'orderParent', targetEntity: DelayedOrder::class)]
    private $delayedOrders;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->delayedOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getExpectedDelivery(): ?\DateTimeImmutable
    {
        return $this->expectedDelivery;
    }

    public function setExpectedDelivery(\DateTimeImmutable $expectedDelivery): self
    {
        $this->expectedDelivery = $expectedDelivery;

        return $this;
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?Address $deliveryAddress): self
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setParent($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getParent() === $this) {
                $orderItem->setParent(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, DelayedOrder>
     */
    public function getDelayedOrders(): Collection
    {
        return $this->delayedOrders;
    }

    public function addDelayedOrder(DelayedOrder $delayedOrder): self
    {
        if (!$this->delayedOrders->contains($delayedOrder)) {
            $this->delayedOrders[] = $delayedOrder;
            $delayedOrder->setOrderParent($this);
        }

        return $this;
    }

    public function removeDelayedOrder(DelayedOrder $delayedOrder): self
    {
        if ($this->delayedOrders->removeElement($delayedOrder)) {
            // set the owning side to null (unless already changed)
            if ($delayedOrder->getOrderParent() === $this) {
                $delayedOrder->setOrderParent(null);
            }
        }

        return $this;
    }
}
