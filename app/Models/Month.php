<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Month extends Model
{
    protected $table = 'payroll_months';

    protected $fillable = [
        'payroll_year_id',
        'month',
        'month_name',
    ];

    protected $casts = [
        'payroll_year_id' => 'integer',
        'month' => 'integer',
    ];

    /**
     * سال مربوط به این ماه
     */
    public function payrollYear(): BelongsTo
    {
        return $this->belongsTo(
            Year::class,
            'payroll_year_id'
        );
    }

    /**
     * هفته‌های مربوط به این ماه
     */
    public function payrollWeeks(): HasMany
    {
        return $this->hasMany(
            Week::class,
            'payroll_month_id'
        );
    }
}
