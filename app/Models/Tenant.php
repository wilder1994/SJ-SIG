<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug', 'nit'];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function primaryContract(): HasOne
    {
        return $this->hasOne(Contract::class)->orderBy('id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
