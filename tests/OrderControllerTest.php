<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderControllerTest extends WebTestCase
{
    public function testOrder(): void
    {
        // @todo use fixtures to create user & items


        $client = static::createClient();

        // Request a specific page
        $crawler = $client->jsonRequest('POST', '/api/login',  ['username'=>'alan@digial.co.uk', 'password' => 'alanjeeves']);

       self::assertResponseIsSuccessful();



       // @todo build up order to submit

        $crawler = $client->jsonRequest('POST', '/v1/orders');
        self::assertResponseIsSuccessful();

    }
}