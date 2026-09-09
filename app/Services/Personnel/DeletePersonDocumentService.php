<?php

namespace App\Services\Personnel;

use App\Models\Course;
use App\Models\PersonDocument;
use App\Support\Files\StoredFileResponder;
use Illuminate\Support\Facades\File;
use RuntimeException;

final class DeletePersonDocumentService
{
    public function execute(PersonDocument $document): void
    {
        if (! $document->hasFile()) {
            throw new RuntimeException('Este registro no tiene un PDF para eliminar.');
        }

        if (! $document->canDelete()) {
            throw new RuntimeException('Solo se puede eliminar durante las 12 horas siguientes a la carga.');
        }

        Course::query()->where('person_document_id', $document->id)->delete();

        $absolute = StoredFileResponder::absolute((string) $document->disk_path);
        if (is_file($absolute)) {
            File::delete($absolute);
        }

        $document->delete();
    }
}
