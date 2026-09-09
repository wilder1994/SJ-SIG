<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlatformAccessTest extends TestCase
{
    use RefreshDatabase;

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
}
