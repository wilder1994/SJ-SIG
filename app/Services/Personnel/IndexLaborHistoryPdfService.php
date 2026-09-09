<?php

namespace App\Services\Personnel;

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
use Throwable;

final class IndexLaborHistoryPdfService
{
    /**
     * @param  list<array{type: LaborHistoryDocumentType, display_name: string, page_from: int, page_to: int}>  $slices
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

    /** @param array{type: LaborHistoryDocumentType, display_name: string, page_from: int, page_to: int} $slice */
    private function storeSlice(DocumentBatch $batch, Person $person, string $source, array $slice): PersonDocument
    {
        $from = max(1, $slice['page_from']);
        $to = max($from, $slice['page_to']);
        $to = min($to, $batch->page_count);

        $relative = sprintf(
            'tenants/%d/people/%d/hv/%s-%s.pdf',
            $person->tenant_id,
            $person->id,
            $slice['type']->value,
            Str::uuid()->toString(),
        );
        $absolute = storage_path('app/'.$relative);
        File::ensureDirectoryExists(dirname($absolute));

        $this->extractPages($source, $absolute, $from, $to, $batch->page_count);

        $name = $slice['display_name'] !== '' ? $slice['display_name'] : $slice['type']->suggestedName($person);
        if (! str_ends_with(strtolower($name), '.pdf')) {
            $name .= '.pdf';
        }

        PersonDocument::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::HojaVida)
            ->where('document_type', $slice['type'])
            ->delete();

        return PersonDocument::query()->create([
            'tenant_id' => $person->tenant_id,
            'person_id' => $person->id,
            'folder' => DocumentFolder::HojaVida,
            'document_type' => $slice['type'],
            'display_name' => $name,
            'page_from' => $from,
            'page_to' => $to,
            'not_applicable' => false,
            'original_name' => $name,
            'disk_path' => $relative,
            'mime' => 'application/pdf',
            'size_bytes' => is_file($absolute) ? (int) filesize($absolute) : 0,
        ]);
    }

    private function extractPages(string $source, string $destination, int $from, int $to, int $pageCount): void
    {
        if ($from === 1 && $to === $pageCount) {
            File::copy($source, $destination);

            return;
        }

        try {
            $pdf = new Fpdi;
            $pdf->setSourceFile($source);
            for ($page = $from; $page <= $to; $page++) {
                $tpl = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'] ?? 'P', [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
            $pdf->Output('F', $destination);
        } catch (Throwable) {
            File::copy($source, $destination);
        }
    }
}
