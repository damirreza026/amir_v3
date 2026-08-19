<?php

namespace Database\Seeders;

use App\Models\Month;
use App\Models\Week;
use Illuminate\Database\Seeder;

class WeekSeeder extends Seeder
{
    /**
     * ثبت هفته‌ها برای تمام ماه‌های موجود
     */
    public function run(): void
    {
        $weekNames = [
            1 => 'هفته اول',
            2 => 'هفته دوم',
            3 => 'هفته سوم',
            4 => 'هفته چهارم',
            5 => 'هفته پنجم',
        ];

        $months = Month::query()->get();

        foreach ($months as $month) {
            foreach ($weekNames as $weekNumber => $weekName) {
                Week::updateOrCreate(
                    [
                        'payroll_month_id' => $month->id,
                        'week' => $weekNumber,
                    ],
                    [
                        'week_name' => $weekName,
                    ]
                );
            }
        }
    }
}
