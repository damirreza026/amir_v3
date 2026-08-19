<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ساخت جدول سال‌های حقوقی
     */
    public function up(): void
    {
        Schema::create('payroll_years', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('year')->unique();

            $table->timestamps();
        });
    }

    /**
     * حذف جدول سال‌های حقوقی
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_years');
    }
};
