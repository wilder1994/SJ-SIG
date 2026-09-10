<?php

namespace Tests\Feature;

use App\Enums\GuardRole;
use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\Post;
use App\Models\Site;
use App\Models\SiteServiceEvent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_table_and_create_button(): void
    {
        [$admin] = $this->adminWithContract();

        $this->actingAs($admin)
            ->get('/instalaciones')
            ->assertOk()
            ->assertSee('Crear instalación')
            ->assertSee('todavía no hay plantas');
    }

    public function test_admin_creates_site_with_posts_and_start_note(): void
    {
        [$admin] = $this->adminWithContract();

        $this->actingAs($admin)
            ->post('/instalaciones', [
                'name' => 'Planta norte',
                'address' => 'Calle 10 # 5-20',
                'city' => 'Cali',
                'service_start' => 'Iniciamos con tres vigilantes en portería.',
                'effective_on' => '2026-03-01',
                'requested_by' => 'Coordinación SOS',
                'posts' => [
                    [
                        'name' => 'Portería',
                        'shift_hours' => 12,
                        'staffings' => [
                            ['role' => GuardRole::Vigilante->value, 'slots' => 3],
                            ['role' => GuardRole::SupervisorPatrulla->value, 'slots' => 1],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $site = Site::query()->firstOrFail();
        $this->assertSame('Planta norte', $site->name);
        $post = $site->posts()->firstOrFail();
        $this->assertSame('Portería', $post->name);
        $this->assertSame(4, $post->guard_slots);
        $this->assertSame(3, $post->staffings()->where('role', GuardRole::Vigilante)->value('slots'));
        $this->assertDatabaseHas('site_service_events', [
            'site_id' => $site->id,
            'kind' => 'inicio',
            'requested_by' => 'Coordinación SOS',
        ]);

        $this->actingAs($admin)
            ->get('/instalaciones/'.$site->id)
            ->assertOk()
            ->assertSee('3 vigilantes')
            ->assertSee('1 supervisor de patrulla')
            ->assertSee('Iniciamos con tres vigilantes');
    }

    public function test_reducing_units_requires_personal_change_note_and_keeps_history(): void
    {
        [$admin, $site, $post] = $this->adminWithStaffedSite();

        $this->actingAs($admin)
            ->put('/instalaciones/'.$site->id, [
                'name' => 'Planta 1',
                'posts' => [[
                    'id' => $post->id,
                    'name' => 'Portería',
                    'shift_hours' => 12,
                    'staffings' => [['role' => GuardRole::Vigilante->value, 'slots' => 2]],
                ]],
            ])
            ->assertSessionHasErrors(['change_reason' => 'Indique el motivo del cambio en personal.']);

        $this->actingAs($admin)
            ->put('/instalaciones/'.$site->id, [
                'name' => 'Planta 1',
                'posts' => [[
                    'id' => $post->id,
                    'name' => 'Portería',
                    'shift_hours' => 12,
                    'staffings' => [['role' => GuardRole::Vigilante->value, 'slots' => 2]],
                ]],
                'change_reason' => 'Solicitud del señor Pepito Pérez. Último turno con tres unidades.',
            ])
            ->assertRedirect(route('sites.show', $site));

        $this->assertSame(2, $post->fresh()->guard_slots);
        $event = SiteServiceEvent::query()->where('site_id', $site->id)->firstOrFail();
        $this->assertSame('Cambio en personal: 3 vigilantes', $event->from_summary);
        $this->assertSame('2 vigilantes', $event->to_summary);
        $this->assertSame(['personal'], $event->snapshot['scopes']);
        $this->assertSame('Cambio en personal', $event->scopesLabel());
    }

    public function test_renaming_site_requires_instalacion_change_note(): void
    {
        [$admin, $site, $post] = $this->adminWithStaffedSite();

        $this->actingAs($admin)
            ->put('/instalaciones/'.$site->id, [
                'name' => 'Planta norte',
                'city' => 'Cali',
                'posts' => [[
                    'id' => $post->id,
                    'name' => 'Portería',
                    'shift_hours' => 12,
                    'staffings' => [['role' => GuardRole::Vigilante->value, 'slots' => 3]],
                ]],
            ])
            ->assertSessionHasErrors(['change_reason' => 'Indique el motivo del cambio en instalación.']);

        $this->actingAs($admin)
            ->put('/instalaciones/'.$site->id, [
                'name' => 'Planta norte',
                'city' => 'Cali',
                'posts' => [[
                    'id' => $post->id,
                    'name' => 'Portería',
                    'shift_hours' => 12,
                    'staffings' => [['role' => GuardRole::Vigilante->value, 'slots' => 3]],
                ]],
                'change_reason' => 'El cliente unificó el nombre comercial de la sede.',
            ])
            ->assertRedirect(route('sites.show', $site));

        $event = SiteServiceEvent::query()->where('site_id', $site->id)->firstOrFail();
        $this->assertSame(['instalacion'], $event->snapshot['scopes']);
        $this->assertStringContainsString('Cambio en instalación', $event->from_summary);
    }

    public function test_renaming_post_and_reducing_units_asks_for_puesto_and_personal(): void
    {
        [$admin, $site, $post] = $this->adminWithStaffedSite();

        $this->actingAs($admin)
            ->put('/instalaciones/'.$site->id, [
                'name' => 'Planta 1',
                'posts' => [[
                    'id' => $post->id,
                    'name' => 'Ronda',
                    'shift_hours' => 12,
                    'staffings' => [['role' => GuardRole::Vigilante->value, 'slots' => 2]],
                ]],
            ])
            ->assertSessionHasErrors(['change_reason' => 'Indique el motivo del cambio en puesto y personal.']);
    }

    public function test_supervisor_cannot_open_create_form(): void
    {
        $this->seed();
        $supervisor = User::query()->where('email', 'supervisor.a@sj-sig.test')->firstOrFail();

        $this->actingAs($supervisor)->get('/instalaciones/nueva')->assertForbidden();
    }

    /** @return array{0: User, 1: Site, 2: Post} */
    private function adminWithStaffedSite(): array
    {
        [$admin, $contract] = $this->adminWithContract();
        $site = Site::query()->create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'code' => 'SOS01',
            'name' => 'Planta 1',
        ]);
        $post = Post::query()->create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'site_id' => $site->id,
            'code' => 'SOS01-01',
            'name' => 'Portería',
            'shift_hours' => 12,
            'guard_slots' => 3,
        ]);
        $post->staffings()->create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'role' => GuardRole::Vigilante,
            'slots' => 3,
        ]);

        return [$admin, $site, $post];
    }

    /** @return array{0: User, 1: Contract} */
    private function adminWithContract(): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'SOS',
            'trade_name' => 'SOS',
            'slug' => 'sos-sitios',
        ]);
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'SOS-1',
            'name' => 'Contrato SOS',
        ]);
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);

        return [$admin, $contract];
    }
}
