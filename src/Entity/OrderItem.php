<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('read')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    #[Groups('read')]
    private int $quantity;

    #[ORM\Column(type: 'integer')]
    #[Groups('read')]
    private int $itemPrice;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    private Item $item;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $parent;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItemPrice(): ?int
    {
        return $this->itemPrice;
    }

    public function setItemPrice(int $itemPrice): self
    {
        $this->itemPrice = $itemPrice;

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getParent(): ?Order
    {
        return $this->parent;
    }

    public function setParent(?Order $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    #[Groups('read')]
    public function getLineTotal(): int
    {
        return $this->getItemPrice() * $this->getQuantity();
    }
}
