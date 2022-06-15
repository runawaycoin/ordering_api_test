<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function dump;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class BaseApiTestCase extends WebTestCase
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
        self::loginAsAdmin();

        // add some items
        self::postJson( '/v1/admin/item', ['name'=>'Record', 'price'=>150]);

    }


    public static function postJson(string $url, array $data, int $expectedStatus = 200)
    {
        self::$client->jsonRequest('POST', $url, $data);
        $jsonResponse = self::$client->getResponse();

        dump('params:' .  json_encode($data));

        self::assertResponseStatusCodeSame($expectedStatus);

        if ($jsonResponse->getStatusCode() == 200 || $jsonResponse->getStatusCode() === 422 || $jsonResponse->getStatusCode() === 401) {

            $json = $jsonResponse->getContent();
            $responseData = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            dump($responseData);
            return $responseData;
        }

        dump($jsonResponse->getContent());
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

        dump($jsonResponse->getContent());
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

        dump($jsonResponse->getContent());
        return null;
    }

    public static function loginAsUser(): int
    {
        $responseData = self::postJson( '/v1/login',  ['email'=>'alan@digial.co.uk', 'password' => 'alanjeeves']);
        $userId = $responseData->id;
        self::assertNotNull($userId);
        return $userId;
    }

    public static function loginAsAdmin(): int
    {
        $responseData = self::postJson( '/v1/login',  ['email'=>'admin@orders.com', 'password' => 'adminadmin']);
        $userId = $responseData->id;
        self::assertNotNull($userId);
        return $userId;
    }
}