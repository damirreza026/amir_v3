<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Year extends Model
{
    protected $table = 'payroll_years';

    protected $fillable = [
        'year',
    ];

    public function months(): HasMany
    {
        return $this->hasMany(
            Month::class,
            'payroll_year_id'
        );
    }
}
