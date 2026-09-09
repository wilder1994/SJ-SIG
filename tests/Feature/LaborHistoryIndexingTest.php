<?php

namespace Tests\Feature;

use App\Enums\AffiliationDocumentType;
use App\Enums\CertificateDocumentType;
use App\Enums\ContractingDocumentType;
use App\Enums\CourseDocumentType;
use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use App\Enums\OtherDocumentType;
use App\Models\DocumentBatch;
use App\Support\Personnel\OtherSupportNamer;
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
            ->assertSee('Contratación')
            ->assertSee('Listado')
            ->assertSee('1/26 indexados')
            ->assertSee('0/9 indexados')
            ->assertSee('1/3 indexados')
            ->assertSee('1/25 indexados')
            ->assertSee('3/8 indexados')
            ->assertSee('0/20 soportes')
            ->assertDontSee('Subir PDF');

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/historia')
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/contratacion')
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/certificados')
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/afiliaciones')
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/cursos')
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/otros')
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
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId.'&cargar=1')
            ->assertOk()
            ->assertSee('Arrastre, pegue o seleccione')
            ->assertSee('Indexar');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/historia', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::HojaVida)
            ->firstOrFail();
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
            ->where('document_type', $type->value)
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

    public function test_internal_user_can_index_contracting_pages(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $type = ContractingDocumentType::ContratoTrabajo;
        $name = $type->suggestedName($person);

        $path = storage_path('framework/testing/contratacion-lote.pdf');
        SimplePdf::writePages($path, ['Pagina contrato']);
        $file = new UploadedFile($path, 'contratacion.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/contratacion', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::Contratacion)
            ->firstOrFail();

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/contratacion/'.$batch->id)
            ->assertOk()
            ->assertSee('Contratación')
            ->assertSee('contrato_trabajo');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/contratacion/'.$batch->id, [
                'slices' => [[
                    'document_type' => $type->value,
                    'display_name' => $name,
                    'pages' => [1],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'folder' => DocumentFolder::Contratacion->value,
            'document_type' => $type->value,
        ]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('1/9 indexados')
            ->assertSee('1/26 indexados');
    }

    public function test_internal_user_can_index_affiliation_pages(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $type = AffiliationDocumentType::Cesantias;
        $name = $type->suggestedName($person);

        $path = storage_path('framework/testing/af-lote.pdf');
        SimplePdf::writePages($path, ['Pagina EPS', 'Pagina cesantias']);
        $file = new UploadedFile($path, 'afiliaciones.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/afiliaciones', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::Afiliaciones)
            ->firstOrFail();

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/afiliaciones/'.$batch->id)
            ->assertOk()
            ->assertSee('Afiliaciones')
            ->assertSee('certificado_cesantias');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/afiliaciones/'.$batch->id, [
                'slices' => [[
                    'document_type' => $type->value,
                    'display_name' => $name,
                    'pages' => [2],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'folder' => DocumentFolder::Afiliaciones->value,
            'document_type' => $type->value,
        ]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('4/8 indexados');
    }

    public function test_internal_user_can_index_certificate_pages(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $type = CertificateDocumentType::ExamenPsicofisico;
        $name = $type->suggestedName($person);

        $path = storage_path('framework/testing/cert-lote.pdf');
        SimplePdf::writePages($path, ['Pagina medico', 'Pagina psicofisico']);
        $file = new UploadedFile($path, 'certificados.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/certificados', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::Certificados)
            ->firstOrFail();

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/certificados/'.$batch->id)
            ->assertOk()
            ->assertSee('Certificados')
            ->assertSee('examen_psicofisico');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/certificados/'.$batch->id, [
                'slices' => [[
                    'document_type' => $type->value,
                    'display_name' => $name,
                    'pages' => [2],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'folder' => DocumentFolder::Certificados->value,
            'document_type' => $type->value,
        ]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('2/3 indexados');
    }

    public function test_internal_user_can_index_supervised_course_pages(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $type = CourseDocumentType::ReentrenamientoVigilancia;
        $name = $type->suggestedName($person);

        $path = storage_path('framework/testing/curso-lote.pdf');
        SimplePdf::writePages($path, ['Acta reentrenamiento']);
        $file = new UploadedFile($path, 'cursos.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/cursos', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::Cursos)
            ->firstOrFail();

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/cursos/'.$batch->id)
            ->assertOk()
            ->assertSee('Cursos y capacitación')
            ->assertSee('reentrenamiento_vigilancia');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/cursos/'.$batch->id, [
                'slices' => [[
                    'document_type' => $type->value,
                    'display_name' => $name,
                    'pages' => [1],
                    'taken_on' => '2026-03-15',
                    'provider' => 'Escuela de la Superintendencia',
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'folder' => DocumentFolder::Cursos->value,
            'document_type' => $type->value,
            'provider' => 'Escuela de la Superintendencia',
        ]);
        $this->assertDatabaseHas('courses', [
            'person_id' => $person->id,
            'course_type' => $type->value,
            'provider' => 'Escuela de la Superintendencia',
        ]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('2/25 indexados');
    }

    public function test_internal_user_can_index_other_support_pages(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $tipo = 'RUT Camara de Comercio';
        $name = OtherSupportNamer::suggestedName($tipo, $person);

        $path = storage_path('framework/testing/otros-lote.pdf');
        SimplePdf::writePages($path, ['Pagina RUT']);
        $file = new UploadedFile($path, 'otros.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/otros', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::Otros)
            ->firstOrFail();

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/otros/'.$batch->id)
            ->assertOk()
            ->assertSee('Otros')
            ->assertSee('escriba el tipo');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/otros/'.$batch->id, [
                'slices' => [[
                    'document_type' => OtherDocumentType::Otro->value,
                    'tipo' => $tipo,
                    'display_name' => $name,
                    'pages' => [1],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'folder' => DocumentFolder::Otros->value,
            'document_type' => OtherDocumentType::Otro->value,
            'display_name' => $tipo,
        ]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('1/20 soportes')
            ->assertSee($tipo);
    }

    public function test_other_support_rejects_catalog_name(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');
        $tipo = 'Contrato de trabajo';

        $path = storage_path('framework/testing/otros-cruzado.pdf');
        SimplePdf::writePages($path, ['Pagina contrato']);
        $file = new UploadedFile($path, 'otros.pdf', 'application/pdf', null, true);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/otros', ['file' => $file])
            ->assertRedirect();

        $batch = DocumentBatch::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::Otros)
            ->firstOrFail();

        $this->actingAs($interno)
            ->from('/documentos/carpeta/'.$person->id.'/otros/'.$batch->id)
            ->post('/documentos/carpeta/'.$person->id.'/otros/'.$batch->id, [
                'slices' => [[
                    'document_type' => OtherDocumentType::Otro->value,
                    'tipo' => $tipo,
                    'display_name' => OtherSupportNamer::suggestedName($tipo, $person),
                    'pages' => [1],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id.'/otros/'.$batch->id)
            ->assertSessionHasErrors('slices.0.tipo');
    }
}
