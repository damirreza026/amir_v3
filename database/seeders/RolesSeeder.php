<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'super_admin',
        ]);

        Role::create([
            'name' => 'admin',
        ]);
        Role::create([
            'name' => 'Warehouse',
        ]);
        Role::create([
            'name' => 'Seller',
        ]);
        Role::create([
            'name' => 'Accountant',
        ]);

    }
}
