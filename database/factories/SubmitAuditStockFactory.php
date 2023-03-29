<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmitAuditStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'stock_count' => $this->faker->numerify("##"),
            'product_id' => Product::factory()->create(),
        ];
    }
}
