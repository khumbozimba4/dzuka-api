<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $sectors = [
            [
                'name' => 'Food Production',
                'description' => 'Production of various food items and culinary products',
                'is_active' => true,
            ],
            [
                'name' => 'Cheese Production',
                'description' => 'Manufacturing of different types of cheese and dairy products',
                'is_active' => true,
            ],
            [
                'name' => 'Starch Production',
                'description' => 'Production of starch-based products and derivatives',
                'is_active' => true,
            ],
            [
                'name' => 'Welding',
                'description' => 'Metal fabrication and welding services',
                'is_active' => true,
            ],
            [
                'name' => 'Carpentry',
                'description' => 'Woodworking and furniture manufacturing',
                'is_active' => true,
            ],
            [
                'name' => 'Cement Production',
                'description' => 'Manufacturing of cement and concrete products',
                'is_active' => true,
            ],
            [
                'name' => 'Automobile',
                'description' => 'Automotive parts and vehicle maintenance services',
                'is_active' => true,
            ],
            [
                'name' => 'Toilet Paper Production',
                'description' => 'Manufacturing of tissue paper and hygiene products',
                'is_active' => true,
            ],
            [
                'name' => 'Candle Production',
                'description' => 'Manufacturing of candles and wax products',
                'is_active' => true,
            ],
            [
                'name' => 'Detergent Production',
                'description' => 'Manufacturing of cleaning products and detergents',
                'is_active' => true,
            ],
            [
                'name' => 'Solar Production',
                'description' => 'Solar panel installation and renewable energy solutions',
                'is_active' => true,
            ],
            [
                'name' => 'Refrigeration',
                'description' => 'Refrigeration systems and cold storage solutions',
                'is_active' => true,
            ],
            [
                'name' => 'Cosmetology',
                'description' => 'Beauty products and cosmetic manufacturing',
                'is_active' => true,
            ],
            [
                'name' => 'Telecentre',
                'description' => 'Telecommunications and IT services',
                'is_active' => true,
            ],
            [
                'name' => 'Agritech',
                'description' => 'Agricultural technology and farming solutions',
                'is_active' => true,
            ],
            [
                'name' => 'Fruit and Veg Value Addition',
                'description' => 'Processing and value addition of fruits and vegetables',
                'is_active' => true,
            ],
            [
                'name' => 'Design and Tailoring',
                'description' => 'Fashion design and clothing manufacturing',
                'is_active' => true,
            ],
            [
                'name' => 'Stationery and Printing',
                'description' => 'Printing services and stationery manufacturing',
                'is_active' => true,
            ],
            [
                'name' => 'Media and Content Development',
                'description' => 'Digital media production and content creation',
                'is_active' => true,
            ],
        ];

        foreach ($sectors as $sector) {
            DB::table('sectors')->insert([
                'name' => $sector['name'],
                'description' => $sector['description'],
                'is_active' => $sector['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
