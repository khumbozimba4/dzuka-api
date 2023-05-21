<?php

namespace AddInventory;

use App\Models\AddInventory;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Supplier;
use Tests\TestCase;

class AddInventoryTest extends TestCase
{
    public function test_get_add_inventories(): void
    {
        AddInventory::factory()->create();
        $response = $this->login()->get('api/addInventory');
        $response->assertOk();
    }

    public function test_add_inventory(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->getKey()
        ]);
        $supplier = Supplier::factory()->create();
        $data = [
            'product_id' => $product->getKey(),
            'supplier_id' => $supplier->getKey(),
            'quantity' => 5,
        ];

        $this->login()->post('api/add-inventory', $data);

        $this->assertDatabaseHas('add_inventories', $data);
    }

    public function test_add_inventory_approve(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->getKey()
        ]);
        $supplier = Supplier::factory()->create();
        $data = [
            'product_id' => $product->getKey(),
            'supplier_id' => $supplier->getKey(),
            'quantity' => 5,
            'unit_cost_price' => 2000
        ];
        Permission::factory()->create([
            'endpoint' => 'api/add-inventory',
            'method' => 'POST',
            'group' => 'add-inventory'
        ]);
        Permission::factory()->create([
            'endpoint' => 'api/add-inventory/{addInventory}/approve',
            'method' => 'PATCH',
            'group' => 'add-inventory'
        ]);
        $this->kampingo->{'role'}->permissions()->attach(Permission::all());
        $this->login()->post('api/add-inventory', $data);
        $response = $this->login()->patch(sprintf('api/add-inventory/%s/approve', 1));
        dd($response);
        $this->assertDatabaseHas('add_inventories', $data);
    }
}
