<?php

namespace Database\Seeders;

use App\Models\Profile;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profile::create([
            'user_id' => 1,
            'role_id' => 2,
            'first_name' => 'AmirReza',
            'last_name' => 'Darvishi',
            'phone' => '09165444698',
            'national_code' => '0987654321',
            'address' => '123 Street',
        ]);
    }
}
