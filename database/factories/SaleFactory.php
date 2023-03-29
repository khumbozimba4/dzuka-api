<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition()
    {
        return [
            'product_id' => Product::factory()->create(),
            'amount' => $this->faker->numerify("##"),
            'quantity' => $this->faker->numerify('##')
        ];
    }
}
