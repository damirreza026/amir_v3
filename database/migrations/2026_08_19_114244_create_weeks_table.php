<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ساخت جدول هفته‌های حقوقی
     */
    public function up(): void
    {
        Schema::create('payroll_weeks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_month_id')
                ->constrained('payroll_months')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('week');

            $table->string('week_name');

            $table->timestamps();

            $table->unique([
                'payroll_month_id',
                'week',
            ]);
        });
    }

    /**
     * حذف جدول هفته‌های حقوقی
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_weeks');
    }
};
