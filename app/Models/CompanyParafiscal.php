<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyParafiscal extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'contract_id', 'period', 'original_name', 'disk_path'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
