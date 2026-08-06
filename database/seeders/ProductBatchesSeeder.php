<?php

namespace Database\Seeders;

use App\Models\ProductBatch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductBatchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductBatch::create([
            'product_id' => 1,
            'profile_id' => 1,
            'sale_price' => '100 Toman',
            'production_date' => '1405-04-20',
            'expiry_date' => '1405-04-31',
            'quantity' => '100',
        ]);
    }
}
