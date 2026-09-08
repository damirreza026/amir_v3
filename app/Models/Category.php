<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * فیلدهای مجاز برای مقداردهی انبوه (Mass Assignment)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * رابطه با محصولات
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
