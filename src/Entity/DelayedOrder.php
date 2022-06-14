<?php

namespace App\Entity;

use App\Repository\DelayedOrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DelayedOrderRepository::class)]
class DelayedOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'delayedOrders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('read')]
    private $orderParent;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups('read')]
    private $created;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups('read')]
    private $expectedDelivery;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderParent(): ?Order
    {
        return $this->orderParent;
    }

    public function setOrderParent(?Order $orderParent): self
    {
        $this->orderParent = $orderParent;

        return $this;
    }

    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(\DateTimeImmutable $created): self
    {
        $this->created = $created;

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
}
