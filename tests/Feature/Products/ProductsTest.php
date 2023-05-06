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
        Permission::factory()->create([
            'endpoint' => 'api/products',
            'method' => 'GET',
            'group' => 'Products'
        ]);
        $this->kampingo->{'role'}->permissions()->attach(Permission::all());
        Product::factory()->create();
        $response =  $this->login()->get('api/products');
        $response->assertOk();
    }

}
