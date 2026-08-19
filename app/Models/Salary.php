<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    protected $table = 'salaries';

    protected $fillable = [
        'profile_id',
        'payroll_year_id',
        'payroll_month_id',
        'payroll_week_id',
        'base_salary',
        'overtime_hours',
        'overtime_amount',
        'deduction_amount',
        'net_salary',
        'status',
        'notes',
    ];

    protected $casts = [
        'profile_id' => 'integer',
        'payroll_year_id' => 'integer',
        'payroll_month_id' => 'integer',
        'payroll_week_id' => 'integer',
        'base_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    /**
     * پروفایل صاحب فیش حقوقی
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(
            Profile::class,
            'profile_id'
        );
    }

    /**
     * سال حقوقی
     */
    public function payrollYear(): BelongsTo
    {
        return $this->belongsTo(
            Year::class,
            'payroll_year_id'
        );
    }

    /**
     * ماه حقوقی
     */
    public function payrollMonth(): BelongsTo
    {
        return $this->belongsTo(
            Month::class,
            'payroll_month_id'
        );
    }

    /**
     * هفته حقوقی
     */
    public function payrollWeek(): BelongsTo
    {
        return $this->belongsTo(
            Week::class,
            'payroll_week_id'
        );
    }
}
