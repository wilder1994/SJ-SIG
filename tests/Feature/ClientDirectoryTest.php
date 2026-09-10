<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\Person;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClientDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_shows_operational_columns_and_page_size(): void
    {
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);
        $this->seedClients(11);

        $this->actingAs($admin)
            ->get('/clientes?per_page=10')
            ->assertOk()
            ->assertSee('Ciudad')
            ->assertSee('Estructura')
            ->assertSee('Instalaciones')
            ->assertSee('Personal')
            ->assertSee('Entrar')
            ->assertSee('Mostrando 1–10 de 11')
            ->assertSee('Cliente 01')
            ->assertSee('Cliente 10');

        $this->actingAs($admin)
            ->get('/clientes?per_page=25')
            ->assertOk()
            ->assertSee('Mostrando 1–11 de 11')
            ->assertSee('Cliente 11');
    }

    public function test_directory_counts_and_enter_switches_contract(): void
    {
        $admin = User::factory()->create(['role' => UserRole::AdminEmpresa]);
        [$first] = $this->seedClients(1);
        $second = $this->makeClient('EPS Occidental', 'eps-occidental', 'Bogotá', 'empresa');
        Site::query()->create([
            'tenant_id' => $second->id,
            'contract_id' => $second->primaryContract->id,
            'code' => 'EPS01',
            'name' => 'Sede norte',
        ]);
        $person = Person::query()->create([
            'tenant_id' => $second->id,
            'document_type' => 'C',
            'document_number' => '55110011',
            'full_name' => 'Vigilante EPS',
        ]);
        $person->contracts()->attach($second->primaryContract->id, [
            'tenant_id' => $second->id,
            'post_id' => null,
        ]);

        $this->actingAs($admin)
            ->get('/clientes?q=Bogotá')
            ->assertOk()
            ->assertSee('EPS Occidental')
            ->assertSee('Bogotá')
            ->assertSee('Empresa')
            ->assertSee('Mostrando 1–1 de 1');

        $this->actingAs($admin)
            ->get('/tablero?contract='.$second->primaryContract->id)
            ->assertOk();

        $this->assertSame($second->primaryContract->id, (int) session('current_contract_id'));
        $this->assertNotSame($first->primaryContract->id, (int) session('current_contract_id'));
    }

    /** @return list<Tenant> */
    private function seedClients(int $count): array
    {
        $clients = [];
        for ($i = 1; $i <= $count; $i++) {
            $clients[] = $this->makeClient(
                sprintf('Cliente %02d', $i),
                'cliente-'.$i,
                'Cali',
                'alcaldia',
            );
        }

        return $clients;
    }

    private function makeClient(string $name, string $slug, string $city, string $structure): Tenant
    {
        $tenant = Tenant::query()->create([
            'name' => $name,
            'slug' => $slug,
            'trade_name' => $name,
            'legal_name' => $name,
            'city' => $city,
            'structure_type' => $structure,
            'nit' => (string) random_int(800000000, 899999999),
            'phone' => '6021112233',
        ]);
        Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => strtoupper($slug),
            'name' => 'Contrato '.$name,
        ]);

        return $tenant->load('primaryContract');
    }
}
