<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Role extends Model
{

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}
