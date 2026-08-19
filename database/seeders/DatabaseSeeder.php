<?php

namespace Database\Seeders;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([UsersSeeder::class]);
        $this->call([RolesSeeder::class]);
        $this->call([ProfileSeeder::class]);
        $this->call(CategoriesSeeder::class);
        $this->call(ProductsSeeder::class);
        $this->call(ProductBatchesSeeder::class);
        $this->call(CustomersSeeder::class);
        $this->call(InvoicSeeder::class);
        $this->call(InvoiceItemSeeder::class);
        $this->call([
            YearSeeder::class,
            MonthSeeder::class,
            WeekSeeder::class,
            SalarySeeder::class,
        ]);


    }
}
