<?php

namespace App\Tests\Controller;

use App\Tests\BaseApiTestCase;

class ApiLoginControllerTest extends BaseApiTestCase
{

    public function testInvalidInput(): void
    {
        $responseData = self::postJson( '/v1/login',  ['email'=>'nouser@no.com', 'password' => 'blahblah'], 401);
        self::assertEquals("Invalid credentials.", $responseData->error);
    }
}