<?php

namespace Database\Seeders;

use App\Models\InvoiceItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InvoiceItem::create([
            'invoice_id' => 1,
            'product_batch_id' => 1,
            'quantity' => 10,
            'price' => 1000000,       // 👈 فقط عدد خام (بدون کاما و toman)
            'subtotal' => 10000000,   // 👈 حاصل ضرب تعداد در قیمت به صورت عدد خام
        ]);
    }
}
