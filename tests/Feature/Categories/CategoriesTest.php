<?php

namespace Categories;

use App\Models\Category;
use App\Models\Permission;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    public function test_get_categories():void
    {
        Permission::factory()->create([
            'endpoint' => 'api/categories',
            'method' => 'GET'
        ]);
        $this->kampingo->{'role'}->permissions()->attach(Permission::all());
        Category::factory()->create();
        $response =  $this->login()->get('api/categories');

        $response->assertOk();
        $this->assertDatabaseCount('categories', 1);
    }

}
