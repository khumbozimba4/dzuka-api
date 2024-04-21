<?php

namespace Supplier;

use App\Models\Permission;
use App\Models\Supplier;
use Tests\TestCase;

class SupplierTest extends TestCase
{

    public function test_get_suppliers():void{
        $supplier = Supplier::factory()->create();

        $response = $this->login()->get('api/suppliers');
        $response->assertOk();
        $this->assertDatabaseCount('suppliers',1);
        $this->assertDatabaseHas('suppliers',[
            'name' => $supplier->{'name'},
            'phone_number'  => $supplier->{'phone_number'},
        ]);
    }

    public function test_create_supplier():void{
        $data = [
            'name' => 'Samson',
            'location' => 'Lilongwe',
            'pin' => '1234',
            'phone_number' => '0996679617',
        ];

        $response = $this->login()->post('api/suppliers', $data);
        $response->assertOk();

        $this->assertDatabaseHas('suppliers',$data);
    }

    public function test_update_supplier():void{
        $supplier =  Supplier::factory()->create([
            'name' => 'Samson',
            'location' => 'Lilongwe',
            'pin' => '1234',
            'phone_number' => '0996679617',
        ]);
        $this->login()->patch(sprintf('api/suppliers/%s', $supplier->getKey()), [
            'name' => 'Sam',
            'location' => 'Lilongwe',
            'pin' => '1234',
            'phone_number' => '0996679617',
        ]);

        $this->assertDatabaseHas('suppliers',[
            'name' => 'Sam',
            'location' => 'Lilongwe',
            'pin' => '1234',
            'phone_number' => '0996679617',
        ]);
    }
}
