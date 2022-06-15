<?php

namespace App\Tests\Controller;

use App\Repository\OrderRepository;
use App\Tests\BaseApiTestCase;
use DateTimeImmutable;

class OrderControllerTest extends BaseApiTestCase
{


    public function testCreateOrder(): void
    {
        $orderRepository = self::getContainer()->get(OrderRepository::class);

        // login as user
        $userId = self::loginAsUser();

        // create addresses
        self::postJson( '/v1/address', ['street'=>'1 Abby Road', 'city'=>'London', 'postcode' => 'W1 2PQ']);
        self::postJson( '/v1/address', ['street'=>'100 Long Road', 'city'=>'London', 'postcode' => 'W1 7PP']);

        // get addresses
        $responseData = self::getJson( '/v1/address');
        self::assertEquals('1 Abby Road', $responseData[0]->street);

        $addressId = $responseData[0]->id;
        self::assertNotNull($addressId);

        $address2Id = $responseData[1]->id;
        self::assertNotNull($address2Id);

        // get items
        $responseData = self::getJson( '/v1/item');
        self::assertEquals('Record', $responseData[0]->name);
        $itemId = $responseData[0]->id;
        self::assertNotNull($itemId);

        // create order
        $responseData = self::postJson( '/v1/orders', ['deliveryAddress' => $addressId,'billingAddress' => $address2Id,   'items'=>[[  'id' =>$itemId, 'quantity' => 2]]]);
        $orderId = $responseData->id;

        self::assertEquals($userId, $responseData->user->id);
        self::assertEquals('alan@digial.co.uk', $responseData->user->email);
        self::assertEquals(2, $responseData->orderItems[0]->quantity);
        self::assertEquals(150, $responseData->orderItems[0]->itemPrice);
        self::assertEquals(300, $responseData->orderItems[0]->lineTotal);
        self::assertEquals("1 Abby Road", $responseData->deliveryAddress->street);
        self::assertEquals("100 Long Road", $responseData->billingAddress->street);
        self::assertEquals("Submitted", $responseData->status);


        // delayed_orders
        self::getJson( '/v1/admin/orders/delayed',403);
        self::loginAsAdmin();
        $responseData = self::getJson( '/v1/admin/orders/delayed');
        self::assertEmpty($responseData);

        // process delayed orders
        $orderRepository->updateDelayedOrders();

        // still none
        $responseData = self::getJson( '/v1/admin/orders/delayed');
        self::assertEmpty($responseData);

        // change order expected date and then process it as delayed
        $yesterday = new DateTimeImmutable('yesterday');
        self::patchJson( '/v1/admin/orders', ['order' => $orderId, 'expectedDelivery' => $yesterday->format('Y-m-d')] );

        // process delayed orders
        $orderRepository->updateDelayedOrders();

        // should have 1 delayed order
        $responseData = self::getJson( '/v1/admin/orders/delayed');
        self::assertCount(1, $responseData);

        // change order status to Delivered
        $responseData = self::patchJson( '/v1/admin/orders', ['order' => $orderId, 'status' => 'Delivered' ] );
        self::assertEquals("Delivered", $responseData->status);

        // process delayed orders
        $orderRepository->updateDelayedOrders();

        // should have 1 row still
        $responseData = self::getJson( '/v1/admin/orders/delayed');
        self::assertCount(1, $responseData);
    }




}