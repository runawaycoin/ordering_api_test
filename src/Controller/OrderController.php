<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Item;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/v1/orders', name: 'order', methods: 'POST')]
    public function order(OrderRepository $orderRepository): Response
    {


        $user = $this->getUser();

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('Submitted');

        $address = new Address();
        $address->setStreet('1 Abby Road');
        $address->setCity('London');
        $address->setPostcode('W1 2PQ');
        $orderRepository->persist($address);

        $order->setBillingAddress($address);
        $order->setDeliveryAddress($address);
        $order->setExpectedDelivery(new DateTimeImmutable('+2 weeks'));

        $item = new Item();
        $item->setName('box');
        $item->setPrice(100);
        $orderRepository->persist($item);


        $orderItem = new OrderItem();
        $orderItem->setItem($item);
        $orderItem->setQuantity(2);
        $orderItem->setItemPrice(  $item->getPrice());
        $orderItem->setParent($order);

        $orderRepository->persist($order);
        $orderRepository->persist($orderItem);

        $orderRepository->flush();

        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }
}
