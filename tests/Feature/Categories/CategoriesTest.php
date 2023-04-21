<?php

namespace Categories;

use App\Models\Category;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    public function test_get_categories():void
    {
        Category::factory()->create();
        $response =  $this->login()->get('api/categories');
        dd($response);

        $response->assertOk();
        $this->assertDatabaseCount('categories', 1);
    }

}
