<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'category_id' => 1,
            'name' => 'Low-fat cows’ milk',
        ]);
        Product::create([
            'category_id' => 1,
            'name' => 'High-fat cows’ milk',
        ]);
        Product::create([
            'category_id' => 1,
            'name' => 'Low-fat goats’ milk',
        ]);
        Product::create([
            'category_id' => 1,
            'name' => 'High-fat goats’ milk',
        ]);
    }
}
