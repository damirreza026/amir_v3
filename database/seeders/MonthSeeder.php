<?php

namespace Database\Seeders;

use App\Models\Month;
use App\Models\Year;
use Illuminate\Database\Seeder;

class MonthSeeder extends Seeder
{
    /**
     * ثبت ماه‌های فارسی برای تمام سال‌های موجود
     */
    public function run(): void
    {
        $monthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        $years = Year::query()->get();

        foreach ($years as $year) {
            foreach ($monthNames as $monthNumber => $monthName) {
                Month::updateOrCreate(
                    [
                        'payroll_year_id' => $year->id,
                        'month' => $monthNumber,
                    ],
                    [
                        'month_name' => $monthName,
                    ]
                );
            }
        }
    }
}
