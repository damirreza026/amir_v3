<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'شیر']);
        Category::create(['name' => 'ماست']);
        Category::create(['name' => 'دوغ']);
        Category::create(['name' => 'کره']);
    }
}
