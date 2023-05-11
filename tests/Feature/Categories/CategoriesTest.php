<?php

namespace Categories;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    public function test_get_categories():void
    {
        Permission::factory()->create([
            'endpoint' => 'api/categories/{category}',
            'method' => 'GET',
            'group' => 'Categories'
        ]);
        $this->kampingo->{'role'}->permissions()->attach(Permission::all());
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->getKey()
        ]);
        $response =  $this->login()->get(sprintf('api/categories/%s',$category->getKey()));

        $response->assertOk();
        $this->assertDatabaseCount('categories', 2);
    }

}
