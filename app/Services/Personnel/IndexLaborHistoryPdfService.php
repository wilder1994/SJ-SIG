<?php

namespace App\Services\Personnel;

use App\Enums\AffiliationDocumentType;
use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use App\Models\DocumentBatch;
use App\Models\Person;
use App\Models\PersonDocument;
use App\Support\Files\StoredFileResponder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use setasign\Fpdi\Fpdi;

final class IndexLaborHistoryPdfService
{
    /**
     * @param  list<array{type: LaborHistoryDocumentType|AffiliationDocumentType, display_name: string, pages: list<int>}>  $slices
     * @return list<PersonDocument>
     */
    public function execute(DocumentBatch $batch, Person $person, array $slices): array
    {
        $source = StoredFileResponder::absolute($batch->disk_path);
        if (! is_file($source)) {
            throw new RuntimeException('El lote PDF ya no está en disco.');
        }

        $created = [];
        foreach ($slices as $slice) {
            $created[] = $this->storeSlice($batch, $person, $source, $slice);
        }

        return $created;
    }

    /** @param array{type: LaborHistoryDocumentType|AffiliationDocumentType, display_name: string, pages: list<int>} $slice */
    private function storeSlice(DocumentBatch $batch, Person $person, string $source, array $slice): PersonDocument
    {
        $pages = $this->normalizePages($slice['pages'], $batch->page_count);
        $folder = $batch->folder ?? DocumentFolder::HojaVida;

        $relative = sprintf(
            'tenants/%d/people/%d/%s/%s-%s.pdf',
            $person->tenant_id,
            $person->id,
            $folder->value,
            $slice['type']->value,
            Str::uuid()->toString(),
        );
        $absolute = storage_path('app/'.$relative);
        File::ensureDirectoryExists(dirname($absolute));

        $this->extractPages($source, $absolute, $pages, $batch->page_count);

        $name = $slice['display_name'] !== '' ? $slice['display_name'] : $slice['type']->suggestedName($person);
        if (! str_ends_with(strtolower($name), '.pdf')) {
            $name .= '.pdf';
        }

        PersonDocument::query()
            ->where('person_id', $person->id)
            ->where('folder', $folder)
            ->where('document_type', $slice['type']->value)
            ->delete();

        return PersonDocument::query()->create([
            'tenant_id' => $person->tenant_id,
            'person_id' => $person->id,
            'folder' => $folder,
            'document_type' => $slice['type']->value,
            'display_name' => $name,
            'page_from' => $pages[0],
            'page_to' => $pages[array_key_last($pages)],
            'pages' => $pages,
            'not_applicable' => false,
            'original_name' => $name,
            'disk_path' => $relative,
            'mime' => 'application/pdf',
            'size_bytes' => is_file($absolute) ? (int) filesize($absolute) : 0,
        ]);
    }

    /**
     * @param  list<int>  $pages
     * @return list<int>
     */
    private function normalizePages(array $pages, int $pageCount): array
    {
        $clean = [];
        foreach ($pages as $page) {
            $page = (int) $page;
            if ($page >= 1 && $page <= $pageCount && ! in_array($page, $clean, true)) {
                $clean[] = $page;
            }
        }

        if ($clean === []) {
            throw new RuntimeException('Seleccione al menos una página válida.');
        }

        return $clean;
    }

    /** @param list<int> $pages */
    private function extractPages(string $source, string $destination, array $pages, int $pageCount): void
    {
        if ($pages === range(1, $pageCount)) {
            File::copy($source, $destination);

            return;
        }

        $pdf = new Fpdi;
        $pdf->setSourceFile($source);
        foreach ($pages as $page) {
            $tpl = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
        }
        $pdf->Output('F', $destination);
    }
}
