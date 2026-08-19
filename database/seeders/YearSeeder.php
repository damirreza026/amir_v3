<?php

namespace Database\Seeders;

use App\Models\Year;
use Illuminate\Database\Seeder;

class YearSeeder extends Seeder
{
    /**
     * اجرای Seeder
     */
    public function run(): void
    {
        Year::updateOrCreate(
            [
                'year' => 1404,
            ],
            [
                'year' => 1404,
            ]
        );

        Year::updateOrCreate(
            [
                'year' => 1405,
            ],
            [
                'year' => 1405,
            ]
        );
    }
}
