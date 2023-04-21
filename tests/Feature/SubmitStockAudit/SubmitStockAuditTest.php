<?php

namespace SubmitStockAudit;

use App\Models\AddInventory;
use App\Models\Category;
use App\Models\Footprint;
use App\Models\Product;
use App\Models\SubmitAuditStock;
use App\Models\Supplier;
use Tests\TestCase;

class SubmitStockAuditTest extends TestCase
{
    public function test_get_submit_audit_stocks():void{
        SubmitAuditStock::factory()->create([
            'user_id' => $this->kampingo->{'id'}
        ]);
        $response = $this->login()->get('api/submit-audit-stock');

        $response->assertOk();
    }

    public function test_submit_audit_stock():void{
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->getKey()
        ]);

        $this->login()->post('api/add-inventory', [
            'product_id' =>$product->getKey(),
            'quantity' => 10,
            'supplier_id'=> $supplier->getKey()
        ]);

        $response = $this->login()->post('api/submit-audit-stock', [
            'product_id' =>$product->getKey(),
            'stock_count' => 5
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('submit_audit_stocks',[
            'product_id' =>$product->getKey(),
            'stock_count' =>5
        ]);
        $this->assertDatabaseCount('sales',1);

        $amount =$product->{'price'} * 5;
        $this->assertDatabaseHas('sales',[
            'product_id' => $product->getKey(),
            'amount' => sprintf("%s.0",$amount),
            'quantity' => 5
        ]);
    }
}
