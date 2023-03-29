<?php

namespace Sale;

use App\Models\Sale;
use Tests\TestCase;

class SalesTest extends TestCase
{
    public function test_get_sales():void{
        Sale::factory()->create();
        $response = $this->login()->get('api/sales');

        $response->assertOk();
    }
}
