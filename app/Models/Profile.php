<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'national_code',
        'address',
        'role_id', // این خط را اضافه کنید

    ];

    public function invoices(): HasMany
    {
        return $this->hasmeny(Invoice::class);
    }

    public function productbatchs(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(
            Salary::class,
            'profile_id'
        );
    }
}
