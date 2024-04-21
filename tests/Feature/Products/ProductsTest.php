<?php

namespace Products;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    public function test_get_products():void
    {
        Product::factory()->create();
        $response =  $this->login()->get('api/products');
        $response->assertOk();
    }

    public function test_delete_product():void
    {
        $product = Product::factory()->create();
        $response =  $this->login()->delete(sprintf('api/products/%s', $product->getKey()));
        $response->assertOk();
        $this->assertDatabaseCount('products', 0);
    }

}
