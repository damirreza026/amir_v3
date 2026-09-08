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
            'name' => 'سوپر ادمین',
        ]);

        Role::create([
            'name' => 'ادمین',
        ]);
        Role::create([
            'name' => 'انباردار',
        ]);
        Role::create([
            'name' => 'فروشنده',
        ]);
        Role::create([
            'name' => 'حسابدار',
        ]);

    }
}
