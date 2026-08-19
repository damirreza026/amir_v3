<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ساخت جدول فیش‌های حقوقی
     */
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('profile_id')
                ->constrained('profiles')
                ->cascadeOnDelete();

            $table->foreignId('payroll_year_id')
                ->constrained('payroll_years')
                ->cascadeOnDelete();

            $table->foreignId('payroll_month_id')
                ->constrained('payroll_months')
                ->cascadeOnDelete();

            $table->foreignId('payroll_week_id')
                ->constrained('payroll_weeks')
                ->cascadeOnDelete();

            $table->decimal('base_salary', 15, 2)
                ->default(0);

            $table->decimal('overtime_hours', 8, 2)
                ->default(0);

            $table->decimal('overtime_amount', 15, 2)
                ->default(0);

            $table->decimal('deduction_amount', 15, 2)
                ->default(0);

            $table->decimal('net_salary', 15, 2)
                ->default(0);

            $table->string('status')
                ->default('pending');

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'profile_id',
                    'payroll_year_id',
                    'payroll_month_id',
                    'payroll_week_id',
                ],
                'salary_unique_per_period'
            );
        });
    }

    /**
     * حذف جدول فیش‌های حقوقی
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
