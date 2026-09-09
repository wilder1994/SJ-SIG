<?php

namespace Tests\Feature;

use App\Enums\LaborHistoryDocumentType;
use App\Models\DocumentBatch;
use App\Models\Person;
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

    public function test_internal_user_can_upload_and_index_a_pdf(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $type = LaborHistoryDocumentType::FotocopiaCedula;
        $name = $type->suggestedName($person);

        $path = storage_path('framework/testing/hv-lote.pdf');
        SimplePdf::write($path, 'Lote historia laboral', 'Pagina de prueba');
        $file = new UploadedFile($path, 'lote.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk()
            ->assertSee('Cargar documentos');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/historia', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()->where('person_id', $person->id)->firstOrFail();
        $this->assertSame(1, $batch->page_count);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/historia/'.$batch->id)
            ->assertOk()
            ->assertSee('Indexar lote');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/historia/'.$batch->id, [
                'slices' => [[
                    'document_type' => $type->value,
                    'display_name' => $name,
                    'page_from' => 1,
                    'page_to' => 1,
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'document_type' => $type->value,
            'display_name' => $name.'.pdf',
            'page_from' => 1,
            'page_to' => 1,
        ]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('2/26 indexados');
    }
}
