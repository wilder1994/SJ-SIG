<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

final class PersonnelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reviews_then_imports_creates_and_updates(): void
    {
        [$admin, $contract] = $this->adminWithContract();
        Person::query()->create([
            'tenant_id' => $contract->tenant_id,
            'document_type' => 'C',
            'document_number' => '11440001',
            'full_name' => 'Ana Vigilante A',
            'eps_name' => 'Sura EPS',
        ])->contracts()->attach($contract->id, [
            'tenant_id' => $contract->tenant_id,
            'post_id' => null,
        ]);

        $file = $this->workbook([
            ['11440001', 'Ana Vigilante A', 'Nueva EPS'],
            ['10880001', 'Carlos Nuevo', 'Sura EPS'],
            ['', 'Sin cedula', ''],
        ]);

        $this->actingAs($admin)
            ->post('/personal/importar/revisar', ['workbook' => $file])
            ->assertRedirect(route('people.import.review'));

        $this->assertSame(1, Person::query()->count());

        $this->actingAs($admin)
            ->get('/personal/importar/revision')
            ->assertOk()
            ->assertSee('Revisar plantilla')
            ->assertSee('Carlos Nuevo')
            ->assertSee('Alta')
            ->assertSee('Actualiza')
            ->assertSee('Sura EPS')
            ->assertSee('Nueva EPS')
            ->assertSee('Cédula vacía')
            ->assertSee('Importar 2 filas válidas');

        $token = (string) session('personnel_import_token');
        $this->assertNotSame('', $token);

        $this->actingAs($admin)
            ->post('/personal/importar', ['token' => $token])
            ->assertRedirect(route('people.index'))
            ->assertSessionHas('status');

        $this->assertSame(2, Person::query()->count());
        $this->assertSame('Nueva EPS', Person::query()->where('document_number', '11440001')->value('eps_name'));
        $this->assertSame('Carlos Nuevo', Person::query()->where('document_number', '10880001')->value('full_name'));
    }

    public function test_import_without_review_token_is_rejected(): void
    {
        [$admin] = $this->adminWithContract();

        $this->actingAs($admin)
            ->post('/personal/importar', [])
            ->assertSessionHasErrors('token');
    }

    public function test_supervisor_cannot_preview_workbook(): void
    {
        $this->seed();
        $supervisor = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();
        $file = $this->workbook([['10880001', 'Carlos Nuevo', 'Sura EPS']]);

        $this->actingAs($supervisor)
            ->post('/personal/importar/revisar', ['workbook' => $file])
            ->assertForbidden();
    }

    /** @return array{0: User, 1: Contract} */
    private function adminWithContract(): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'Cliente import',
            'slug' => 'cliente-import',
        ]);
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'IMP-1',
            'name' => 'Contrato import',
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::AdminEmpresa,
        ]);

        return [$admin, $contract];
    }

    /** @param  list<list<string>>  $rows */
    private function workbook(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['cedula', 'nombre', 'nombre_eps'], null, 'A1');
        $sheet->fromArray(['ayuda', 'ayuda', 'ayuda'], null, 'A2');
        $sheet->fromArray($rows, null, 'A3');

        $dir = storage_path('framework/testing');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $path = $dir.DIRECTORY_SEPARATOR.uniqid('ficha_', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'ficha_empleados SJ-SIG.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }
}
