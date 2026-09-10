<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlatformAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_clients_sees_personnel_empty_state(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::AdminEmpresa,
        ]);

        $this->actingAs($admin)
            ->get('/personal')
            ->assertOk()
            ->assertSee('No hay personal registrado')
            ->assertSee('Crea el primero en Clientes')
            ->assertSee('Nuevo cliente')
            ->assertDontSee('Importar')
            ->assertDontSee('Revisar')
            ->assertDontSee('name="workbook"', false);
    }

    public function test_internal_user_without_clients_sees_personnel_empty_state(): void
    {
        $interno = User::factory()->create([
            'role' => UserRole::Interno,
        ]);

        $this->actingAs($interno)
            ->get('/personal')
            ->assertOk()
            ->assertSee('No hay personal registrado')
            ->assertSee('Crea el primero en Clientes')
            ->assertDontSee('Nuevo cliente')
            ->assertDontSee('Nuevo empleado')
            ->assertDontSee('Revisar')
            ->assertDontSee('name="workbook"', false);
    }

    public function test_admin_with_client_and_no_people_sees_create_employee_prompt(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Cliente vacío',
            'slug' => 'cliente-vacio',
        ]);
        Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'VAC-1',
            'name' => 'Contrato vacío',
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::AdminEmpresa,
        ]);

        $this->actingAs($admin)
            ->get('/personal')
            ->assertOk()
            ->assertSee('No hay personal registrado')
            ->assertSee('todavía no hay vigilantes')
            ->assertSee('Nuevo empleado')
            ->assertSee('Carga masiva')
            ->assertSee('Revisar')
            ->assertSee('name="workbook"', false)
            ->assertDontSee('Nuevo cliente');
    }

    public function test_admin_without_clients_sees_empty_states_on_modules(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::AdminEmpresa,
        ]);

        $this->actingAs($admin);

        $this->get('/clientes')->assertOk()->assertSee('No hay clientes')->assertSee('Nuevo cliente');
        $this->get('/instalaciones')->assertOk()->assertSee('No hay instalaciones')->assertSee('Nuevo cliente');
        $this->get('/documentos')->assertOk()->assertSee('No hay carpetas de personal')->assertSee('Nuevo cliente');
        $this->get('/parafiscales')->assertOk()->assertSee('No hay parafiscales')->assertSee('Nuevo cliente');
        $this->get('/electronica')->assertOk()->assertSee('No hay activos electrónicos')->assertSee('Nuevo cliente');
        $this->get('/servicios')->assertOk()->assertSee('No hay servicios registrados')->assertSee('Nuevo cliente');
        $this->get('/novedades')->assertOk()->assertSee('No hay novedades')->assertSee('Nuevo cliente');
        $this->get('/equipo')->assertOk()->assertSee('No hay equipo de operaciones')->assertSee('Nuevo cliente');
    }

    public function test_entity_supervisor_cannot_open_users_or_clients(): void
    {
        $this->seed();
        $supervisor = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();

        $this->actingAs($supervisor)->get('/usuarios')->assertForbidden();
        $this->actingAs($supervisor)->get('/clientes')->assertForbidden();
    }

    public function test_internal_user_cannot_manage_users_but_sees_all_clients_switcher(): void
    {
        $this->seed();
        $interno = User::query()->where('email', 'interno@sj-sig.test')->firstOrFail();

        $this->actingAs($interno)->get('/usuarios')->assertForbidden();
        $this->actingAs($interno)->get('/tablero')->assertOk()->assertSee('Alcaldía Municipio A');
    }

    public function test_supervisor_sees_only_own_operations_team(): void
    {
        $this->seed();
        $supervisorA = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();
        $supervisorB = User::query()->where('email', 'supervisor.b@sj-sig.test')->firstOrFail();

        $this->actingAs($supervisorA)
            ->get('/equipo')
            ->assertOk()
            ->assertSee('Coordinador operaciones Alcaldía Municipio A')
            ->assertDontSee('Coordinador operaciones Entidad Contratante B');

        $this->actingAs($supervisorB)
            ->get('/equipo')
            ->assertOk()
            ->assertSee('Coordinador operaciones Entidad Contratante B')
            ->assertDontSee('Coordinador operaciones Alcaldía Municipio A');
    }

    public function test_profile_is_read_only(): void
    {
        $this->seed();
        $supervisor = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();

        $this->actingAs($supervisor)
            ->get('/perfil')
            ->assertOk()
            ->assertSee('Solo consulta')
            ->assertDontSee('name="email"');
    }

    public function test_supervisor_sees_own_sites_and_cannot_create_structure(): void
    {
        $this->seed();
        $supervisorA = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();
        $supervisorB = User::query()->where('email', 'supervisor.b@sj-sig.test')->firstOrFail();

        $this->actingAs($supervisorA)
            ->get('/instalaciones')
            ->assertOk()
            ->assertSee('Planta 1')
            ->assertSee('Portería')
            ->assertSee('3 vigilantes')
            ->assertDontSee('name="code"');

        $this->actingAs($supervisorA)
            ->post('/instalaciones', ['code' => 'X1', 'name' => 'Hack'])
            ->assertForbidden();

        $this->actingAs($supervisorB)
            ->get('/instalaciones')
            ->assertOk()
            ->assertSee('Planta 1')
            ->assertDontSee('Ana Vigilante');
    }
}
