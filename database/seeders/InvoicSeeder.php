<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Invoice::create([
            'customer_id' => 1,
            'profile_id' => 1,
            'total_price' => 1000000, // 👈 فقط عدد، بدون هیچ متنی
            'invoice_date' => '2026-08-01', // 👈 تاریخ معتبر میلادی (Y-m-d)
        ]);
    }
}
