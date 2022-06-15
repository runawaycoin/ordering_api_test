<?php

namespace App\Tests\Controller;

use App\Tests\BaseApiTestCase;

class ItemControllerTest extends BaseApiTestCase
{



    public function testAdminPerms(): void
    {
        // login as user, not admin
        self::loginAsUser();

        // add item
        // 403 Forbidden
        self::postJson( '/v1/admin/item', ['name'=>'Record', 'price'=>150], 403);

    }

    public function testInvalidInput(): void
    {
        self::loginAsAdmin();

        // add some items
        $responseData = self::postJson( '/v1/admin/item', ['name'=>'', 'price'=>150], 422);
        self::assertEquals("name", $responseData->errors[0]->field);
        self::assertEquals("This value should not be blank.", $responseData->errors[0]->message);

        $responseData = self::postJson( '/v1/admin/item', ['name'=>'box', 'price'=>0], 422);
        self::assertEquals("price", $responseData->errors[0]->field);
        self::assertEquals("This value should be greater than 0.", $responseData->errors[0]->message);

        $responseData = self::postJson( '/v1/admin/item', ['name'=>'box', 'price'=> -150], 422);
        self::assertEquals("price", $responseData->errors[0]->field);
        self::assertEquals("This value should be greater than 0.", $responseData->errors[0]->message);

        $responseData = self::postJson( '/v1/admin/item', ['name'=>null, 'price'=>-150], 422);
        self::assertEquals("price", $responseData->errors[0]->field);
        self::assertEquals("This value should be greater than 0.", $responseData->errors[0]->message);
        self::assertEquals("name", $responseData->errors[1]->field);
        self::assertEquals("This value should not be blank.", $responseData->errors[1]->message);
    }



}