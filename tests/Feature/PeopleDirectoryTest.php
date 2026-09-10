<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\Person;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PeopleDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_is_full_width_with_count_and_page_size(): void
    {
        [$admin, $contract] = $this->adminWithContract();
        $this->seedPeople($contract, 11);

        $this->actingAs($admin)
            ->get('/personal?per_page=10')
            ->assertOk()
            ->assertSee('Carga masiva')
            ->assertSee('Nuevo empleado')
            ->assertSee('Estado')
            ->assertSee('Cargo')
            ->assertSee('Teléfono')
            ->assertSee('Mostrando 1–10 de 11')
            ->assertSee('Vigilante 01')
            ->assertSee('Vigilante 10')
            ->assertDontSee('Vigilante 11')
            ->assertSee('name="workbook"', false);

        $this->actingAs($admin)
            ->get('/personal?per_page=25')
            ->assertOk()
            ->assertSee('Mostrando 1–11 de 11')
            ->assertSee('Vigilante 11');
    }

    public function test_ficha_shows_excel_fields_in_groups(): void
    {
        [$admin, $contract] = $this->adminWithContract();
        $person = $this->makePerson($contract, [
            'document_number' => '11001100',
            'full_name' => 'Ana Completa',
            'phone' => '3001112233',
            'email' => 'ana@sj-sig.test',
            'job_code' => 'VIG',
            'eps_name' => 'Sura EPS',
            'eps_code' => 'EPS001',
            'afp_name' => 'Porvenir',
            'address' => 'Calle 10 # 5-20',
            'birth_date' => '1990-03-15',
            'blood_type' => 'O+',
            'hired_on' => '2024-02-01',
        ]);

        $this->actingAs($admin)
            ->get('/personal/'.$person->id)
            ->assertOk()
            ->assertSee('Ana Completa')
            ->assertSee('Identidad')
            ->assertSee('Contacto y residencia')
            ->assertSee('Vinculación laboral')
            ->assertSee('Seguridad social')
            ->assertSee('15/03/1990')
            ->assertSee('Calle 10 # 5-20')
            ->assertSee('O+')
            ->assertSee('Sura EPS · EPS001')
            ->assertSee('Porvenir')
            ->assertSee('Editar')
            ->assertSee('Carpeta (0)')
            ->assertDontSee('archivos en carpeta');
    }

    public function test_admin_creates_and_edits_full_ficha(): void
    {
        [$admin] = $this->adminWithContract();

        $this->actingAs($admin)
            ->get('/personal/nuevo')
            ->assertOk()
            ->assertSee('Fecha de nacimiento')
            ->assertSee('Nivel de riesgo ARL');

        $this->actingAs($admin)
            ->post('/personal', [
                'document_type' => 'C',
                'document_number' => '99887766',
                'full_name' => 'Carlos Nuevo',
                'birth_date' => '1988-07-20',
                'residence_city' => 'Cali',
                'address' => 'Cra 1 # 2-3',
                'phone' => '3105556677',
                'job_code' => 'ESC',
                'eps_name' => 'Nueva EPS',
                'arl_name' => 'Sura',
                'arl_risk_level' => '4',
            ])
            ->assertRedirect();

        $person = Person::query()->where('document_number', '99887766')->firstOrFail();
        $this->assertSame('Cali', $person->residence_city);
        $this->assertSame('4', $person->arl_risk_level);

        $this->actingAs($admin)
            ->get('/personal/'.$person->id.'/editar')
            ->assertOk()
            ->assertSee('Editar ficha')
            ->assertSee('Carlos Nuevo');

        $this->actingAs($admin)
            ->put('/personal/'.$person->id, [
                'document_type' => 'C',
                'document_number' => '99887766',
                'full_name' => 'Carlos Nuevo',
                'residence_city' => 'Palmira',
                'address' => 'Cra 1 # 2-3',
                'phone' => '3105556677',
                'job_code' => 'ESC',
                'eps_name' => 'Sura EPS',
                'arl_name' => 'Sura',
                'arl_risk_level' => '5',
            ])
            ->assertRedirect(route('people.show', $person));

        $this->assertSame('Palmira', $person->fresh()->residence_city);
        $this->assertSame('Sura EPS', $person->fresh()->eps_name);
        $this->assertSame('5', $person->fresh()->arl_risk_level);
    }

    public function test_supervisor_cannot_open_edit_form(): void
    {
        $this->seed();
        $supervisor = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();
        $person = Person::query()->where('document_number', '11440001')->firstOrFail();

        $this->actingAs($supervisor)
            ->get('/personal/'.$person->id.'/editar')
            ->assertForbidden();
    }

    /** @return array{0: User, 1: Contract} */
    private function adminWithContract(): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'Cliente personal',
            'slug' => 'cliente-personal',
        ]);
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'PER-1',
            'name' => 'Contrato personal',
        ]);
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);

        return [$admin, $contract];
    }

    /** @param  array<string, mixed>  $overrides */
    private function makePerson(Contract $contract, array $overrides = []): Person
    {
        $person = Person::query()->create(array_merge([
            'tenant_id' => $contract->tenant_id,
            'document_type' => 'C',
            'document_number' => '11001100',
            'full_name' => 'Persona prueba',
        ], $overrides));
        $person->contracts()->attach($contract->id, [
            'tenant_id' => $contract->tenant_id,
            'post_id' => null,
        ]);

        return $person;
    }

    private function seedPeople(Contract $contract, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $person = Person::query()->create([
                'tenant_id' => $contract->tenant_id,
                'document_type' => 'C',
                'document_number' => (string) (11000000 + $i),
                'full_name' => sprintf('Vigilante %02d', $i),
                'job_code' => 'VIG',
                'phone' => '30000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'eps_name' => 'Sura EPS',
            ]);
            $person->contracts()->attach($contract->id, [
                'tenant_id' => $contract->tenant_id,
                'post_id' => null,
            ]);
        }
    }
}
