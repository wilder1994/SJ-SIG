<?php

namespace App\Services\Personnel;

use App\Enums\DocumentFolder;
use App\Models\Contract;
use App\Models\Person;
use App\Models\PersonDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class StorePersonDocumentService
{
    public function execute(Contract $contract, Person $person, DocumentFolder $folder, UploadedFile $file, ?string $expiresOn = null): PersonDocument
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $relative = sprintf(
            'tenants/%d/contracts/%d/people/%d/%s/%s.%s',
            $contract->tenant_id,
            $contract->id,
            $person->id,
            $folder->value,
            Str::uuid()->toString(),
            $extension,
        );

        $original = $file->getClientOriginalName();
        $mime = $file->getMimeType() ?: 'application/pdf';
        $size = (int) $file->getSize();

        $absolute = storage_path('app/'.$relative);
        File::ensureDirectoryExists(dirname($absolute));
        $file->move(dirname($absolute), basename($absolute));

        return PersonDocument::query()->create([
            'tenant_id' => $contract->tenant_id,
            'person_id' => $person->id,
            'folder' => $folder,
            'original_name' => $original,
            'disk_path' => $relative,
            'mime' => $mime,
            'size_bytes' => $size,
            'expires_on' => $expiresOn,
        ]);
    }
}
