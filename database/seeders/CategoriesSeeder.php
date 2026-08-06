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
        Category::create(['name' => 'milk']);
        Category::create(['name' => 'yogurt']);
        Category::create(['name' => 'dough']);
        Category::create(['name' => 'butter']);
    }
}
