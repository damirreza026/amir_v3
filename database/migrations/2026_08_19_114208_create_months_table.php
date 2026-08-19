<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ساخت جدول ماه‌های حقوقی
     */
    public function up(): void
    {
        Schema::create('payroll_months', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_year_id')
                ->constrained('payroll_years')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('month');

            $table->string('month_name');

            $table->timestamps();

            $table->unique([
                'payroll_year_id',
                'month',
            ]);
        });
    }

    /**
     * حذف جدول ماه‌های حقوقی
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_months');
    }
};
