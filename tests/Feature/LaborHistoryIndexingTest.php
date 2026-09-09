<?php

namespace Tests\Feature;

use App\Enums\LaborHistoryDocumentType;
use App\Models\DocumentBatch;
use App\Models\Person;
use App\Models\PersonDocument;
use App\Models\User;
use App\Support\Files\SimplePdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

final class LaborHistoryIndexingTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_sees_history_listing_and_cannot_upload(): void
    {
        $this->seed();
        $supervisor = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();

        $this->actingAs($supervisor)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('Historia Laboral')
            ->assertSee('Listado')
            ->assertSee('1/26 indexados')
            ->assertDontSee('Subir PDF');

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/historia')
            ->assertForbidden();
    }

    public function test_internal_user_can_index_non_contiguous_pages(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $type = LaborHistoryDocumentType::FotocopiaCedula;
        $name = $type->suggestedName($person);

        $path = storage_path('framework/testing/hv-lote.pdf');
        SimplePdf::writePages($path, ['Pagina uno', 'Pagina dos', 'Pagina tres']);
        $file = new UploadedFile($path, 'lote.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/historia', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()->where('person_id', $person->id)->firstOrFail();
        $this->assertSame(3, $batch->page_count);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/historia/'.$batch->id)
            ->assertOk()
            ->assertSee('Indexar lote')
            ->assertSee('Marque las páginas');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/historia/'.$batch->id.'/ver')
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/historia/'.$batch->id, [
                'slices' => [[
                    'document_type' => $type->value,
                    'display_name' => $name,
                    'pages' => [1, 3],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $document = PersonDocument::query()
            ->where('person_id', $person->id)
            ->where('document_type', $type)
            ->firstOrFail();

        $this->assertSame([1, 3], $document->pages);
        $this->assertSame(1, $document->page_from);
        $this->assertSame(3, $document->page_to);
        $this->assertTrue(is_file(storage_path('app/'.$document->disk_path)));

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('2/26 indexados');
    }
}
