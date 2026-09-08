<?php

namespace Database\Seeders;

use App\Models\Month;
use App\Models\Week;
use Illuminate\Database\Seeder;

class WeekSeeder extends Seeder
{
    /**
     * ثبت هفته‌ها برای تمام ماه‌های موجود:
     * ۶ ماه اول سال (فروردین تا شهریور): ۵ هفته
     * ۶ ماه دوم سال (مهر تا اسفند): ۴ هفته
     */
    public function run(): void
    {
        $allWeekNames = [
            1 => 'هفته اول',
            2 => 'هفته دوم',
            3 => 'هفته سوم',
            4 => 'هفته چهارم',
            5 => 'هفته پنجم',
        ];

        $months = Month::query()->get();

        foreach ($months as $month) {
            // تعیین تعداد هفته‌ها بر اساس شماره ماه (۱ تا ۱۲)
            $weekCount = ((int) $month->month <= 6) ? 5 : 4;

            // دریافت نام هفته‌ها متناسب با تعداد مشخص شده
            $currentMonthWeeks = array_slice($allWeekNames, 0, $weekCount, true);

            // در صورتی که قبلاً برای ۶ ماه دوم هفته پنجم ثبت شده باشد، آن را حذف می‌کند
            Week::where('payroll_month_id', $month->id)
                ->where('week', '>', $weekCount)
                ->delete();

            foreach ($currentMonthWeeks as $weekNumber => $weekName) {
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
