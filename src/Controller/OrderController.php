<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\AddressRepository;
use App\Repository\DelayedOrderRepository;
use App\Repository\ItemRepository;
use App\Repository\OrderRepository;
use App\Utils\BaseController;
use DateTimeImmutable;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function in_array;
use function json_decode;
use function property_exists;
use const JSON_THROW_ON_ERROR;

class OrderController extends BaseController
{

    /**
     * @OA\Post(description="New Order",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(  property="name", type="string",  example="ORDER" ),
     *               ),
     *           )
     *       )
     *   ),
     *  )
     */
    #[Route('/v1/orders', name: 'new_order', methods: 'POST')]
    public function newOrder(OrderRepository $orderRepository, ItemRepository $itemRepository, AddressRepository $addressRepository,  Request $request): Response
    {
        // {"deliveryAddress" : 63,"billingAddress" : 63,"items":[{"id":44,"quantity":2}]}

        $user = $this->getUser();
        $requestData = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);

        if ($user) {

            $order = new Order();
            $order->setUser($user);
            $order->setStatus(Order::STATUS_SUBMITTED);


            $billingAddress = $addressRepository->findOneBy( ['id'=> $requestData->billingAddress, 'user' => $user]);
            $deliveryAddress = $addressRepository->findOneBy( ['id'=> $requestData->deliveryAddress, 'user' => $user]);

            if ($billingAddress === null || $deliveryAddress === null) {
                return $this->json('Address not found', 422);
            }

            $order->setBillingAddress($billingAddress);
            $order->setDeliveryAddress($deliveryAddress);
            $order->setExpectedDelivery(new DateTimeImmutable('+2 weeks'));


            foreach ($requestData->items as $itemData) {
                $orderItem = new OrderItem();
                $item = $itemRepository->find($itemData->id);
                if ($item) {
                    $orderItem->setItem($item);
                    $orderItem->setQuantity($itemData->quantity);
                    $orderItem->setItemPrice($item->getPrice());
                    $order->addOrderItem($orderItem);
                    $orderRepository->persist($orderItem);
                }
            }

            $orderRepository->persist($order);


            $orderRepository->flush();

            $data = $this->serialize($order, ['read']);
            return $this->json($data);
        }

        return $this->json('');
    }


    /**
     *
     * @OA\Parameter(name="order",in="query", required=false, @OA\Schema(type="number", example=4))
     * @OA\Parameter(name="status",in="query", required=false, @OA\Schema(type="string", example="delivered"))
     */
    #[Route('/v1/orders', name: 'orders_list', methods: 'GET')]
    public function ordersList(OrderRepository $orderRepository,  Request $request): Response
    {
        $user = $this->getUser();

        if ($request->query->has('order')) {
            $orders = $orderRepository->findBy(['user'=>$user, 'id' => $request->query->get('order')]);
        } elseif ($request->query->has('status')) {
            $orders = $orderRepository->findBy(['user'=>$user, 'status' => $request->query->get('status')]);
        } else {
            $orders = $orderRepository->findBy(['user'=>$user]);
        }

        $data = $this->serialize($orders, ['read']);

        return $this->json($data);
    }




    /**
     * @OA\Patch(description="Update Order Status",
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(  property="order", type="number",  example=1 ),
     *               @OA\Property(  property="status", type="string",  example="Delivered" ),
     *               @OA\Property(  property="expectedDelivery", type="date",  example="2022-06-12" )
     *               ),
     *           )
     *       )
     *   ),
     *  )
     */
    #[Route('/v1/admin/orders', name: 'order_update', methods: 'PATCH')]
    public function orderUpdate(OrderRepository $orderRepository,  Request $request): Response
    {

        $requestData = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $order = $orderRepository->findOneBy([ 'id'=>$requestData->order]);

        if ($order !== null) {

            if (property_exists($requestData, 'status') && in_array($requestData->status, [Order::STATUS_SUBMITTED, Order::STATUS_DELIVERED], true)) {
                $order->setStatus($requestData->status);
            }

            if (property_exists($requestData, 'expectedDelivery') && $requestData->expectedDelivery != '') {
                $order->setExpectedDelivery(new DateTimeImmutable($requestData->expectedDelivery ));
            }

            $orderRepository->flush();

            $data = $this->serialize($order, ['read']);
            return $this->json($data);
        }

        return $this->json('',422);
    }



    #[Route('/v1/admin/orders/delayed', name: 'delayed_orders', methods: 'GET')]
    public function delayedOrders(DelayedOrderRepository $delayedOrderRepository): Response
    {
        $delayedOrders = $delayedOrderRepository->findAll();

        $data = $this->serialize($delayedOrders, ['read']);

        return $this->json($data);
    }
}
