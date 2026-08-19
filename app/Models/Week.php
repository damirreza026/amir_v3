<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Week extends Model
{
    protected $table = 'payroll_weeks';

    protected $fillable = [
        'payroll_month_id',
        'week',
        'week_name',
    ];

    protected $casts = [
        'payroll_month_id' => 'integer',
        'week' => 'integer',
    ];

    public function payrollMonth(): BelongsTo
    {
        return $this->belongsTo(
            Month::class,
            'payroll_month_id'
        );
    }
}
