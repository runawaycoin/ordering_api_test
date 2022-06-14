<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function dump;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class OrderControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected static $client;



    public function setUp(): void
    {
        // init app
        self::$client = static::createClient();

        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $entityManager = self::getContainer()->get(UserRepository::class);

        // create user
        $user = new User();
        $user->setEmail('alan@digial.co.uk');
        $user->setRoles([ 'ROLE_USER']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'alanjeeves');
        $user->setPassword($hashedPassword);
        $entityManager->persist($user);

        // create admin
        $admin = new User();
        $admin->setEmail('admin@orders.com');
        $admin->setRoles([ 'ROLE_ADMIN']);
        $hashedPassword = $passwordHasher->hashPassword($admin, 'adminadmin');
        $admin->setPassword($hashedPassword);
        $entityManager->persist($admin);

        $entityManager->flush();

        // login as admin
        $responseData = self::loginAsAdmin();


        // add some items
        self::postJson( '/v1/admin/item', ['name'=>'Record', 'price'=>150]);

    }

    public function tearDown(): void
    {

    }

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


    public function testAdminPerms(): void
    {
        // login as user, not admin
        self::loginAsUser();

        // add item
        // 403 Forbidden
        self::postJson( '/v1/admin/item', ['name'=>'Record', 'price'=>150], 403);

    }

    public static function postJson(string $url, array $data, int $expectedStatus = 200)
    {
        self::$client->jsonRequest('POST', $url, $data);
        $jsonResponse = self::$client->getResponse();

        dump('params:' .  json_encode($data));

        self::assertResponseStatusCodeSame($expectedStatus);

        if ($jsonResponse->getStatusCode() == 200) {
            self::assertResponseIsSuccessful();
            $json = $jsonResponse->getContent();
            $responseData = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            dump($responseData);
            return $responseData;
        }
        return null;
    }

    public static function patchJson(string $url, array $data, int $expectedStatus = 200)
    {
        self::$client->jsonRequest('PATCH', $url, $data);
        $jsonResponse = self::$client->getResponse();

        dump('params:' .  json_encode($data));

        self::assertResponseStatusCodeSame($expectedStatus);

        if ($jsonResponse->getStatusCode() == 200) {
            self::assertResponseIsSuccessful();
            $json = $jsonResponse->getContent();
            $responseData = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            dump($responseData);
            return $responseData;
        }
        return null;
    }

    public static function getJson(string $url, int $expectedStatus = 200)
    {
        self::$client->jsonRequest('GET', $url);
        $jsonResponse = self::$client->getResponse();

        self::assertResponseStatusCodeSame($expectedStatus);

        if ($jsonResponse->getStatusCode() == 200) {
            self::assertResponseIsSuccessful();
            $json = $jsonResponse->getContent();
            return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        }
        return null;
    }

    public static function loginAsUser(): int
    {
        $responseData = self::postJson( '/v1/login',  ['username'=>'alan@digial.co.uk', 'password' => 'alanjeeves']);
        $userId = $responseData->id;
        self::assertNotNull($userId);
        return $userId;
    }

    public static function loginAsAdmin(): int
    {
        $responseData = self::postJson( '/v1/login',  ['username'=>'admin@orders.com', 'password' => 'adminadmin']);
        $userId = $responseData->id;
        self::assertNotNull($userId);
        return $userId;
    }

}