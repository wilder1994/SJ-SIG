<?php

namespace App\Services\Personnel;

use App\Enums\DocumentFolder;
use App\Models\Contract;
use App\Models\DocumentBatch;
use App\Models\Person;
use App\Support\Files\PdfPageReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class StoreLaborHistoryBatchService
{
    public function execute(Contract $contract, Person $person, UploadedFile $file, DocumentFolder $folder = DocumentFolder::HojaVida): DocumentBatch
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $relative = sprintf(
            'tenants/%d/contracts/%d/people/%d/%s/batches/%s.%s',
            $contract->tenant_id,
            $contract->id,
            $person->id,
            $folder->value,
            Str::uuid()->toString(),
            $extension,
        );

        $original = $file->getClientOriginalName() ?: basename($relative);
        $mime = $file->getMimeType() ?: 'application/pdf';

        $absolute = storage_path('app/'.$relative);
        File::ensureDirectoryExists(dirname($absolute));
        $file->move(dirname($absolute), basename($absolute));

        $pages = str_ends_with(strtolower($relative), '.pdf')
            ? PdfPageReader::count($absolute)
            : 1;

        return DocumentBatch::query()->create([
            'tenant_id' => $contract->tenant_id,
            'person_id' => $person->id,
            'folder' => $folder,
            'original_name' => $original,
            'disk_path' => $relative,
            'mime' => $mime,
            'page_count' => $pages,
        ]);
    }
}
