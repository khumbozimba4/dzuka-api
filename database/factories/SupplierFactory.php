<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'pin' => $this->faker->numerify("####"),
            'phone_number' => $this->faker->numerify('088#######'),
            'location' => $this->faker->address
        ];
    }
}
