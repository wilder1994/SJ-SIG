<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug'];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
