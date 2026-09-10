<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClientLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_client_with_location(): void
    {
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);

        $this->actingAs($admin)
            ->post('/clientes', [
                'person_kind' => 'juridica',
                'structure_type' => 'alcaldia',
                'trade_name' => 'Alcaldía A',
                'legal_name' => 'Municipio de Ejemplo',
                'document_type' => 'NIT',
                'document_number' => '890900111',
                'contact_email' => 'contacto@ejemplo.gov.co',
                'phone' => '6021112233',
                'legal_rep_name' => 'Ana Alcaldesa',
                'legal_rep_email' => 'alcaldesa@ejemplo.gov.co',
                'address' => 'Calle 10 # 5-20, Cali',
                'city' => 'Cali',
                'department' => 'Valle del Cauca',
                'lat' => '3.4516000',
                'lng' => '-76.5320000',
                'place_id' => 'test-place',
                'starts_on' => '2026-01-01',
            ])
            ->assertRedirect(route('clients.index'));

        $tenant = Tenant::query()->firstOrFail();
        $contract = Contract::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('Municipio de Ejemplo', $tenant->name);
        $this->assertSame('890900111', $tenant->nit);
        $this->assertSame('Cali', $tenant->city);
        $this->assertEqualsWithDelta(3.4516, (float) $tenant->lat, 0.0001);
        $this->assertSame(0, Site::query()->count());
        $this->assertSame($contract->id, (int) session('current_contract_id'));
    }

    public function test_stale_session_contract_falls_back_to_existing_client(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'EPS Occidental',
            'slug' => 'eps-occidental',
        ]);
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'EPS-1',
            'name' => 'Servicio de vigilancia 2026',
        ]);
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);

        $this->actingAs($admin)
            ->withSession(['current_contract_id' => 9999])
            ->get('/instalaciones')
            ->assertOk()
            ->assertSee('Servicio de vigilancia 2026')
            ->assertSee('No hay instalaciones')
            ->assertSee('Crear instalación')
            ->assertDontSee('Sin cliente activo');

        $this->assertSame($contract->id, (int) session('current_contract_id'));

        $this->actingAs($admin)
            ->withSession(['current_contract_id' => 9999])
            ->get('/tablero')
            ->assertOk()
            ->assertSee('Servicio de vigilancia 2026')
            ->assertDontSee('Sin cliente activo');
    }

    public function test_admin_can_create_site_with_location(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Cliente mapa',
            'slug' => 'cliente-mapa',
            'lat' => 3.45,
            'lng' => -76.53,
        ]);
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'MAP-1',
            'name' => 'Contrato mapa',
        ]);
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);

        $this->actingAs($admin)
            ->withSession(['current_contract_id' => $contract->id])
            ->post('/instalaciones', [
                'name' => 'Planta 1',
                'address' => 'Carrera 1 # 2-3, Cali',
                'city' => 'Cali',
                'department' => 'Valle del Cauca',
                'lat' => '3.4600000',
                'lng' => '-76.5400000',
            ])
            ->assertRedirect();

        $site = Site::query()->firstOrFail();
        $this->assertSame('CM01', $site->code);
        $this->assertSame('Carrera 1 # 2-3, Cali', $site->address);
        $this->assertEqualsWithDelta(3.46, (float) $site->lat, 0.0001);
    }

    public function test_dashboard_exposes_client_and_site_map_points(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Cliente mapa',
            'slug' => 'cliente-mapa',
            'address' => 'Calle 10, Cali',
            'lat' => 3.45,
            'lng' => -76.53,
        ]);
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'MAP-1',
            'name' => 'Contrato mapa',
        ]);
        Site::query()->create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'code' => 'P1',
            'name' => 'Planta 1',
            'address' => 'Bodega norte',
            'lat' => 3.46,
            'lng' => -76.54,
        ]);
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);

        $this->actingAs($admin)
            ->withSession(['current_contract_id' => $contract->id])
            ->get('/tablero')
            ->assertOk()
            ->assertSee('data-overview-map', false)
            ->assertSee('Cliente mapa')
            ->assertSee('Planta 1')
            ->assertSee('Satélite')
            ->assertSee('Calle')
            ->assertSee('data-map-type-btn="hybrid"', false)
            ->assertSee('map-dot client', false)
            ->assertSee('map-dot site', false);
    }
}
