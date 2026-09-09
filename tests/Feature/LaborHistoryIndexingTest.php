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
use App\Models\Person;
use App\Models\PersonDocument;
use App\Models\User;
use App\Support\Files\SimplePdf;
use App\Support\Personnel\OtherSupportNamer;
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
            ->assertDontSee('Cargar documentos')
            ->assertDontSee('Indexar lote')
            ->assertDontSee('No aplica');

        $this->actingAs($supervisor)
            ->post('/documentos/carpeta/'.$person->id.'/lote')
            ->assertForbidden();
    }

    public function test_internal_user_can_index_non_contiguous_pages(): void
    {
        [$interno, $person] = $this->internoAndPerson();
        $type = LaborHistoryDocumentType::FotocopiaCedula;
        $batch = $this->uploadLote($interno, $person, ['Pagina uno', 'Pagina dos', 'Pagina tres'], 'hv-lote.pdf');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->assertOk()
            ->assertSee('Indexar lote')
            ->assertSee('Carpeta')
            ->assertSee('Historia Laboral')
            ->assertSee('Marque las páginas');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id.'/ver')
            ->assertOk();

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id, [
                'slices' => [[
                    'folder' => DocumentFolder::HojaVida->value,
                    'document_type' => $type->value,
                    'display_name' => $type->suggestedName($person),
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

    public function test_internal_user_can_index_mixed_folders_from_one_pdf(): void
    {
        [$interno, $person] = $this->internoAndPerson();
        $hv = LaborHistoryDocumentType::FotocopiaCedula;
        $contract = ContractingDocumentType::ContratoTrabajo;
        $batch = $this->uploadLote($interno, $person, ['Cedula', 'Contrato'], 'mixto.pdf');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id, [
                'slices' => [
                    [
                        'folder' => DocumentFolder::HojaVida->value,
                        'document_type' => $hv->value,
                        'display_name' => $hv->suggestedName($person),
                        'pages' => [1],
                    ],
                    [
                        'folder' => DocumentFolder::Contratacion->value,
                        'document_type' => $contract->value,
                        'display_name' => $contract->suggestedName($person),
                        'pages' => [2],
                    ],
                ],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('2/26 indexados')
            ->assertSee('1/9 indexados');
    }

    public function test_internal_user_can_index_contracting_pages(): void
    {
        [$interno, $person] = $this->internoAndPerson();
        $type = ContractingDocumentType::ContratoTrabajo;
        $batch = $this->uploadLote($interno, $person, ['Pagina contrato'], 'contratacion.pdf');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->assertOk()
            ->assertSee('Contratación')
            ->assertSee('contrato_trabajo');

        $this->postIndexed($interno, $person, $batch, DocumentFolder::Contratacion, $type->value, $type->suggestedName($person));

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'folder' => DocumentFolder::Contratacion->value,
            'document_type' => $type->value,
        ]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('1/9 indexados');
    }

    public function test_internal_user_can_index_affiliation_pages(): void
    {
        [$interno, $person] = $this->internoAndPerson();
        $type = AffiliationDocumentType::Cesantias;
        $batch = $this->uploadLote($interno, $person, ['Pagina EPS', 'Pagina cesantias'], 'afiliaciones.pdf');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->assertOk()
            ->assertSee('Afiliaciones')
            ->assertSee('certificado_cesantias');

        $this->postIndexed($interno, $person, $batch, DocumentFolder::Afiliaciones, $type->value, $type->suggestedName($person), [2]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('4/8 indexados');
    }

    public function test_internal_user_can_index_certificate_pages(): void
    {
        [$interno, $person] = $this->internoAndPerson();
        $type = CertificateDocumentType::ExamenPsicofisico;
        $batch = $this->uploadLote($interno, $person, ['Pagina medico', 'Pagina psicofisico'], 'certificados.pdf');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->assertOk()
            ->assertSee('Certificados')
            ->assertSee('examen_psicofisico');

        $this->postIndexed($interno, $person, $batch, DocumentFolder::Certificados, $type->value, $type->suggestedName($person), [2]);

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id)
            ->assertOk()
            ->assertSee('2/3 indexados');
    }

    public function test_internal_user_can_index_supervised_course_pages(): void
    {
        [$interno, $person] = $this->internoAndPerson();
        $type = CourseDocumentType::ReentrenamientoVigilancia;
        $batch = $this->uploadLote($interno, $person, ['Acta reentrenamiento'], 'cursos.pdf');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->assertOk()
            ->assertSee('Cursos y capacitación')
            ->assertSee('reentrenamiento_vigilancia');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id, [
                'slices' => [[
                    'folder' => DocumentFolder::Cursos->value,
                    'document_type' => $type->value,
                    'display_name' => $type->suggestedName($person),
                    'pages' => [1],
                    'taken_on' => '2026-03-15',
                    'provider' => 'Escuela de la Superintendencia',
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

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
        [$interno, $person] = $this->internoAndPerson();
        $tipo = 'RUT Camara de Comercio';
        $batch = $this->uploadLote($interno, $person, ['Pagina RUT'], 'otros.pdf');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->assertOk()
            ->assertSee('Otros')
            ->assertSee('varias carpetas');

        $this->actingAs($interno)
            ->post('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id, [
                'slices' => [[
                    'folder' => DocumentFolder::Otros->value,
                    'document_type' => OtherDocumentType::Otro->value,
                    'tipo' => $tipo,
                    'display_name' => OtherSupportNamer::suggestedName($tipo, $person),
                    'pages' => [1],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);

        $this->assertDatabaseHas('person_documents', [
            'person_id' => $person->id,
            'folder' => DocumentFolder::Otros->value,
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
        [$interno, $person] = $this->internoAndPerson();
        $tipo = 'Contrato de trabajo';
        $batch = $this->uploadLote($interno, $person, ['Pagina contrato'], 'otros-cruzado.pdf');

        $this->actingAs($interno)
            ->from('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->post('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id, [
                'slices' => [[
                    'folder' => DocumentFolder::Otros->value,
                    'document_type' => OtherDocumentType::Otro->value,
                    'tipo' => $tipo,
                    'display_name' => OtherSupportNamer::suggestedName($tipo, $person),
                    'pages' => [1],
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id)
            ->assertSessionHasErrors('slices.0.tipo');
    }

    /** @return array{0: User, 1: Person} */
    private function internoAndPerson(): array
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();
        $contractId = $person->contracts()->value('contracts.id');

        $this->actingAs($interno)
            ->get('/documentos/carpeta/'.$person->id.'?contract='.$contractId)
            ->assertOk()
            ->assertSee('Cargar documentos')
            ->assertSee('Indexar lote')
            ->assertSee('Un PDF por lote')
            ->assertSee('No aplica');

        return [$interno, $person];
    }

    /** @param list<string> $pages */
    private function uploadLote(User $user, Person $person, array $pages, string $filename): DocumentBatch
    {
        $path = storage_path('framework/testing/'.$filename);
        SimplePdf::writePages($path, $pages);
        $file = new UploadedFile($path, $filename, 'application/pdf', null, true);

        $this->actingAs($user)
            ->post('/documentos/carpeta/'.$person->id.'/lote', ['file' => $file])
            ->assertRedirect();

        return DocumentBatch::query()
            ->where('person_id', $person->id)
            ->latest('id')
            ->firstOrFail();
    }

    /** @param list<int> $pages */
    private function postIndexed(
        User $user,
        Person $person,
        DocumentBatch $batch,
        DocumentFolder $folder,
        string $type,
        string $name,
        array $pages = [1],
    ): void {
        $this->actingAs($user)
            ->post('/documentos/carpeta/'.$person->id.'/lote/'.$batch->id, [
                'slices' => [[
                    'folder' => $folder->value,
                    'document_type' => $type,
                    'display_name' => $name,
                    'pages' => $pages,
                ]],
            ])
            ->assertRedirect('/documentos/carpeta/'.$person->id);
    }
}
