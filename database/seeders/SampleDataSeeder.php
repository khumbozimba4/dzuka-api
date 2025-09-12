<?php

// Seeder: SampleDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // First, let's add Hospitality sector (it wasn't in the original 19)
        $hospitalityId = DB::table('sectors')->insertGetId([
            'name' => 'Hospitality',
            'description' => 'Hotel, restaurant and hospitality services',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $foodProductionId = DB::table('sectors')->where('name', 'Food Production')->first()->id;

        // Create sample ingredients
        $ingredients = [
            ['name' => 'Flour (White)', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 1.50, 'current_stock' => 100],
            ['name' => 'Sugar', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 2.00, 'current_stock' => 80],
            ['name' => 'Butter', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 8.00, 'current_stock' => 25],
            ['name' => 'Eggs', 'unit_of_measurement' => 'dozen', 'cost_per_unit' => 3.50, 'current_stock' => 50],
            ['name' => 'Milk', 'unit_of_measurement' => 'liters', 'cost_per_unit' => 1.25, 'current_stock' => 60],
            ['name' => 'Vanilla Extract', 'unit_of_measurement' => 'ml', 'cost_per_unit' => 0.15, 'current_stock' => 500],
            ['name' => 'Cocoa Powder', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 6.00, 'current_stock' => 20],
            ['name' => 'Baking Powder', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 4.00, 'current_stock' => 15],
            ['name' => 'Salt', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 0.80, 'current_stock' => 30],
            ['name' => 'Tomatoes', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 2.50, 'current_stock' => 40],
            ['name' => 'Onions', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 1.80, 'current_stock' => 35],
            ['name' => 'Chicken Breast', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 12.00, 'current_stock' => 20],
            ['name' => 'Beef Mince', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 15.00, 'current_stock' => 18],
            ['name' => 'Rice', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 2.20, 'current_stock' => 50],
            ['name' => 'Pasta', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 3.00, 'current_stock' => 30],
            ['name' => 'Olive Oil', 'unit_of_measurement' => 'liters', 'cost_per_unit' => 8.50, 'current_stock' => 15],
            ['name' => 'Cheese (Mozzarella)', 'unit_of_measurement' => 'kg', 'cost_per_unit' => 18.00, 'current_stock' => 12],
            ['name' => 'Bread', 'unit_of_measurement' => 'loaves', 'cost_per_unit' => 1.50, 'current_stock' => 25],
        ];

        $ingredientIds = [];
        foreach ($ingredients as $ingredient) {
            $ingredientIds[] = DB::table('ingredients')->insertGetId(array_merge($ingredient, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Food Production Products
        $foodProducts = [
            [
                'name' => 'Artisan Bread Loaf',
                'description' => 'Fresh baked artisan bread made with premium ingredients',
                'image' => 'images/products/artisan-bread.jpg',
                'selling_price' => 8.00,
                'sector_id' => $foodProductionId,
            ],
            [
                'name' => 'Chocolate Cupcakes (6-pack)',
                'description' => 'Rich chocolate cupcakes with vanilla frosting',
                'image' => 'images/products/chocolate-cupcakes.jpg',
                'selling_price' => 15.00,
                'sector_id' => $foodProductionId,
            ],
            [
                'name' => 'Fresh Pasta (500g)',
                'description' => 'Homemade fresh pasta using traditional methods',
                'image' => 'images/products/fresh-pasta.jpg',
                'selling_price' => 12.00,
                'sector_id' => $foodProductionId,
            ],
            [
                'name' => 'Meat Pies (4-pack)',
                'description' => 'Traditional meat pies with beef and vegetables',
                'image' => 'images/products/meat-pies.jpg',
                'selling_price' => 20.00,
                'sector_id' => $foodProductionId,
            ],
            [
                'name' => 'Vanilla Sponge Cake',
                'description' => 'Light and fluffy vanilla sponge cake',
                'image' => 'images/products/vanilla-cake.jpg',
                'selling_price' => 25.00,
                'sector_id' => $foodProductionId,
            ],
            [
                'name' => 'Chicken Curry (Ready to Heat)',
                'description' => 'Homemade chicken curry with aromatic spices',
                'image' => 'images/products/chicken-curry.jpg',
                'selling_price' => 18.00,
                'sector_id' => $foodProductionId,
            ],
            [
                'name' => 'Beef Stew (Family Size)',
                'description' => 'Slow-cooked beef stew with vegetables',
                'image' => 'images/products/beef-stew.jpg',
                'selling_price' => 22.00,
                'sector_id' => $foodProductionId,
            ],
        ];

        // Hospitality Products
        $hospitalityProducts = [
            [
                'name' => 'Wedding Cake (3-tier)',
                'description' => 'Custom decorated 3-tier wedding cake serves 50-60 people',
                'image' => 'images/products/wedding-cake.jpg',
                'selling_price' => 150.00,
                'sector_id' => $hospitalityId,
            ],
            [
                'name' => 'Birthday Party Catering (10 people)',
                'description' => 'Complete birthday party catering package',
                'image' => 'images/products/birthday-catering.jpg',
                'selling_price' => 80.00,
                'sector_id' => $hospitalityId,
            ],
            [
                'name' => 'Corporate Lunch Box (per person)',
                'description' => 'Healthy lunch box with sandwich, fruit, and drink',
                'image' => 'images/products/lunch-box.jpg',
                'selling_price' => 12.00,
                'sector_id' => $hospitalityId,
            ],
            [
                'name' => 'High Tea Set (serves 4)',
                'description' => 'Traditional high tea with scones, sandwiches, and pastries',
                'image' => 'images/products/high-tea.jpg',
                'selling_price' => 45.00,
                'sector_id' => $hospitalityId,
            ],
            [
                'name' => 'Event Buffet (per person)',
                'description' => 'Full buffet service for events and functions',
                'image' => 'images/products/event-buffet.jpg',
                'selling_price' => 35.00,
                'sector_id' => $hospitalityId,
            ],
            [
                'name' => 'Cocktail Party Canapes (50 pieces)',
                'description' => 'Assorted gourmet canapes for cocktail parties',
                'image' => 'images/products/canapes.jpg',
                'selling_price' => 65.00,
                'sector_id' => $hospitalityId,
            ],
            [
                'name' => 'Traditional Braai Pack (serves 8)',
                'description' => 'Complete braai package with meat, sides, and salads',
                'image' => 'images/products/braai-pack.jpg',
                'selling_price' => 120.00,
                'sector_id' => $hospitalityId,
            ],
        ];

        $allProducts = array_merge($foodProducts, $hospitalityProducts);

        foreach ($allProducts as $product) {
            $productId = DB::table('commodities')->insertGetId(array_merge($product, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Add sample ingredients for each product (Bill of Quantities)
            $this->addProductIngredients($productId, $product['name'], $ingredientIds);
        }
    }

    private function addProductIngredients($productId, $productName, $ingredientIds)
    {
        // Define ingredients for each product
        $productIngredients = [
            'Artisan Bread Loaf' => [
                ['ingredient_index' => 0, 'quantity' => 0.5, 'name' => 'Flour (White)'], // 500g flour
                ['ingredient_index' => 8, 'quantity' => 0.01, 'name' => 'Salt'], // 10g salt
                ['ingredient_index' => 5, 'quantity' => 10, 'name' => 'Vanilla Extract'], // 10ml
            ],

            'Chocolate Cupcakes (6-pack)' => [
                ['ingredient_index' => 0, 'quantity' => 0.3, 'name' => 'Flour (White)'], // 300g flour
                ['ingredient_index' => 1, 'quantity' => 0.2, 'name' => 'Sugar'], // 200g sugar
                ['ingredient_index' => 2, 'quantity' => 0.1, 'name' => 'Butter'], // 100g butter
                ['ingredient_index' => 3, 'quantity' => 0.25, 'name' => 'Eggs'], // 3 eggs
                ['ingredient_index' => 6, 'quantity' => 0.05, 'name' => 'Cocoa Powder'], // 50g cocoa
                ['ingredient_index' => 4, 'quantity' => 0.15, 'name' => 'Milk'], // 150ml milk
            ],

            'Fresh Pasta (500g)' => [
                ['ingredient_index' => 0, 'quantity' => 0.4, 'name' => 'Flour (White)'], // 400g flour
                ['ingredient_index' => 3, 'quantity' => 0.33, 'name' => 'Eggs'], // 4 eggs
                ['ingredient_index' => 15, 'quantity' => 0.02, 'name' => 'Olive Oil'], // 20ml olive oil
                ['ingredient_index' => 8, 'quantity' => 0.005, 'name' => 'Salt'], // 5g salt
            ],

            'Meat Pies (4-pack)' => [
                ['ingredient_index' => 0, 'quantity' => 0.4, 'name' => 'Flour (White)'], // 400g flour
                ['ingredient_index' => 12, 'quantity' => 0.4, 'name' => 'Beef Mince'], // 400g beef
                ['ingredient_index' => 10, 'quantity' => 0.2, 'name' => 'Onions'], // 200g onions
                ['ingredient_index' => 2, 'quantity' => 0.1, 'name' => 'Butter'], // 100g butter
                ['ingredient_index' => 3, 'quantity' => 0.17, 'name' => 'Eggs'], // 2 eggs
            ],

            'Vanilla Sponge Cake' => [
                ['ingredient_index' => 0, 'quantity' => 0.25, 'name' => 'Flour (White)'], // 250g flour
                ['ingredient_index' => 1, 'quantity' => 0.25, 'name' => 'Sugar'], // 250g sugar
                ['ingredient_index' => 2, 'quantity' => 0.25, 'name' => 'Butter'], // 250g butter
                ['ingredient_index' => 3, 'quantity' => 0.42, 'name' => 'Eggs'], // 5 eggs
                ['ingredient_index' => 5, 'quantity' => 15, 'name' => 'Vanilla Extract'], // 15ml
                ['ingredient_index' => 7, 'quantity' => 0.01, 'name' => 'Baking Powder'], // 10g
            ],

            'Chicken Curry (Ready to Heat)' => [
                ['ingredient_index' => 11, 'quantity' => 0.6, 'name' => 'Chicken Breast'], // 600g chicken
                ['ingredient_index' => 10, 'quantity' => 0.2, 'name' => 'Onions'], // 200g onions
                ['ingredient_index' => 9, 'quantity' => 0.3, 'name' => 'Tomatoes'], // 300g tomatoes
                ['ingredient_index' => 15, 'quantity' => 0.05, 'name' => 'Olive Oil'], // 50ml oil
                ['ingredient_index' => 13, 'quantity' => 0.2, 'name' => 'Rice'], // 200g rice
            ],

            'Beef Stew (Family Size)' => [
                ['ingredient_index' => 12, 'quantity' => 0.8, 'name' => 'Beef Mince'], // 800g beef
                ['ingredient_index' => 10, 'quantity' => 0.3, 'name' => 'Onions'], // 300g onions
                ['ingredient_index' => 9, 'quantity' => 0.4, 'name' => 'Tomatoes'], // 400g tomatoes
                ['ingredient_index' => 15, 'quantity' => 0.08, 'name' => 'Olive Oil'], // 80ml oil
            ],

            'Wedding Cake (3-tier)' => [
                ['ingredient_index' => 0, 'quantity' => 2.0, 'name' => 'Flour (White)'], // 2kg flour
                ['ingredient_index' => 1, 'quantity' => 1.8, 'name' => 'Sugar'], // 1.8kg sugar
                ['ingredient_index' => 2, 'quantity' => 1.5, 'name' => 'Butter'], // 1.5kg butter
                ['ingredient_index' => 3, 'quantity' => 2.5, 'name' => 'Eggs'], // 30 eggs
                ['ingredient_index' => 5, 'quantity' => 100, 'name' => 'Vanilla Extract'], // 100ml
                ['ingredient_index' => 4, 'quantity' => 1.0, 'name' => 'Milk'], // 1L milk
            ],

            'Birthday Party Catering (10 people)' => [
                ['ingredient_index' => 11, 'quantity' => 2.0, 'name' => 'Chicken Breast'], // 2kg chicken
                ['ingredient_index' => 13, 'quantity' => 1.0, 'name' => 'Rice'], // 1kg rice
                ['ingredient_index' => 17, 'quantity' => 2, 'name' => 'Bread'], // 2 loaves
                ['ingredient_index' => 1, 'quantity' => 0.5, 'name' => 'Sugar'], // 500g sugar
                ['ingredient_index' => 0, 'quantity' => 0.8, 'name' => 'Flour (White)'], // 800g flour
            ],

            'Corporate Lunch Box (per person)' => [
                ['ingredient_index' => 17, 'quantity' => 0.2, 'name' => 'Bread'], // 1/5 loaf
                ['ingredient_index' => 11, 'quantity' => 0.15, 'name' => 'Chicken Breast'], // 150g
                ['ingredient_index' => 16, 'quantity' => 0.05, 'name' => 'Cheese (Mozzarella)'], // 50g
            ],

            'High Tea Set (serves 4)' => [
                ['ingredient_index' => 0, 'quantity' => 0.4, 'name' => 'Flour (White)'], // 400g flour
                ['ingredient_index' => 2, 'quantity' => 0.2, 'name' => 'Butter'], // 200g butter
                ['ingredient_index' => 1, 'quantity' => 0.3, 'name' => 'Sugar'], // 300g sugar
                ['ingredient_index' => 3, 'quantity' => 0.33, 'name' => 'Eggs'], // 4 eggs
                ['ingredient_index' => 4, 'quantity' => 0.5, 'name' => 'Milk'], // 500ml milk
            ],

            'Event Buffet (per person)' => [
                ['ingredient_index' => 11, 'quantity' => 0.2, 'name' => 'Chicken Breast'], // 200g
                ['ingredient_index' => 13, 'quantity' => 0.15, 'name' => 'Rice'], // 150g
                ['ingredient_index' => 10, 'quantity' => 0.1, 'name' => 'Onions'], // 100g
                ['ingredient_index' => 9, 'quantity' => 0.1, 'name' => 'Tomatoes'], // 100g
            ],

            'Cocktail Party Canapes (50 pieces)' => [
                ['ingredient_index' => 17, 'quantity' => 1, 'name' => 'Bread'], // 1 loaf
                ['ingredient_index' => 16, 'quantity' => 0.3, 'name' => 'Cheese (Mozzarella)'], // 300g
                ['ingredient_index' => 11, 'quantity' => 0.5, 'name' => 'Chicken Breast'], // 500g
                ['ingredient_index' => 2, 'quantity' => 0.2, 'name' => 'Butter'], // 200g
            ],

            'Traditional Braai Pack (serves 8)' => [
                ['ingredient_index' => 12, 'quantity' => 1.5, 'name' => 'Beef Mince'], // 1.5kg beef
                ['ingredient_index' => 11, 'quantity' => 1.0, 'name' => 'Chicken Breast'], // 1kg chicken
                ['ingredient_index' => 10, 'quantity' => 0.5, 'name' => 'Onions'], // 500g onions
                ['ingredient_index' => 9, 'quantity' => 0.6, 'name' => 'Tomatoes'], // 600g tomatoes
                ['ingredient_index' => 17, 'quantity' => 2, 'name' => 'Bread'], // 2 loaves
            ],
        ];

        if (isset($productIngredients[$productName])) {
            foreach ($productIngredients[$productName] as $ingredient) {
                $ingredientId = $ingredientIds[$ingredient['ingredient_index']];
                $costPerUnit = $this->getIngredientCost($ingredient['ingredient_index']);

                DB::table('commodity_ingredients')->insert([
                    'commodity_id' => $productId,
                    'ingredient_id' => $ingredientId,
                    'quantity_required' => $ingredient['quantity'],
                    'cost_per_unit' => $costPerUnit,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function getIngredientCost($index)
    {
        $costs = [1.50, 2.00, 8.00, 3.50, 1.25, 0.15, 6.00, 4.00, 0.80, 2.50, 1.80, 12.00, 15.00, 2.20, 3.00, 8.50, 18.00, 1.50];
        return $costs[$index] ?? 1.00;
    }

}