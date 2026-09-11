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
            'name' => 'شیر کم چرب گاو',
        ]);
        Product::create([
            'category_id' => 1,
            'name' => 'شیر پر چرب گاو',
        ]);
        Product::create([
            'category_id' => 1,
            'name' => 'شیر گاومیش',
        ]);
        Product::create([
            'category_id' => 1,
            'name' => 'شیر بز',
        ]);
    }
}
