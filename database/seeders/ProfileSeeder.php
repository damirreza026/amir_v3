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
            'role_id' => 1,
            'first_name' => 'امیررضا',
            'last_name' => 'درویشی',
            'phone' => '09165444698',
            'national_code' => '5320067895',
            'address' => 'استان بوشهر،شهر سعدآباد،کوچه فرهنگیان',
        ]);
    }
}
