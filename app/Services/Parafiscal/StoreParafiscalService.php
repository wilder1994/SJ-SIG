<?php

namespace App\Services\Parafiscal;

use App\Models\CompanyParafiscal;
use App\Models\Contract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class StoreParafiscalService
{
    public function execute(Contract $contract, string $period, UploadedFile $file): CompanyParafiscal
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $relative = sprintf(
            'tenants/%d/contracts/%d/parafiscals/%s/%s.%s',
            $contract->tenant_id,
            $contract->id,
            $period,
            Str::uuid()->toString(),
            $extension,
        );

        $original = $file->getClientOriginalName();

        $absolute = storage_path('app/'.$relative);
        File::ensureDirectoryExists(dirname($absolute));
        $file->move(dirname($absolute), basename($absolute));

        return CompanyParafiscal::query()->create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'period' => $period,
            'original_name' => $original,
            'disk_path' => $relative,
        ]);
    }
}
