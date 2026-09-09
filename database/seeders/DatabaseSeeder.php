<?php

namespace Database\Seeders;

use App\Enums\DocumentFolder;
use App\Enums\ElectronicAssetKind;
use App\Enums\NoveltyStatus;
use App\Enums\UserRole;
use App\Models\CompanyParafiscal;
use App\Models\Contract;
use App\Models\Course;
use App\Models\ElectronicAsset;
use App\Models\Maintenance;
use App\Models\Novelty;
use App\Models\Person;
use App\Models\PersonDocument;
use App\Models\Post;
use App\Models\ServiceDelivery;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make((string) env('SEED_PASSWORD', 'Sig2026!'));

        User::query()->create([
            'name' => 'Administración SJ',
            'email' => 'admin@sj-sig.test',
            'password' => $password,
            'role' => UserRole::AdminEmpresa,
        ]);

        $alcaldia = $this->seedTenant(
            slug: 'alcaldia-a',
            tenantName: 'Alcaldía Municipio A',
            contractCode: 'ALC-2026',
            contractName: 'Vigilancia y seguridad privada 2026',
            city: 'Cali',
        );

        $entidadB = $this->seedTenant(
            slug: 'entidad-b',
            tenantName: 'Entidad Contratante B',
            contractCode: 'ENT-B-2026',
            contractName: 'Esquema de seguridad sedes B',
            city: 'Bogotá',
        );

        User::query()->create([
            'name' => 'Operador Alcaldía',
            'email' => 'operador.a@sj-sig.test',
            'password' => $password,
            'role' => UserRole::Operador,
            'tenant_id' => $alcaldia['tenant']->id,
            'contract_id' => null,
        ]);

        User::query()->create([
            'name' => 'Técnico electrónica',
            'email' => 'tecnico@sj-sig.test',
            'password' => $password,
            'role' => UserRole::TecnicoElectronica,
            'tenant_id' => $alcaldia['tenant']->id,
        ]);

        User::query()->create([
            'name' => 'Supervisor Contratación Pública',
            'email' => 'supervisor.a@sj-sig.test',
            'password' => $password,
            'role' => UserRole::SupervisorEntidad,
            'tenant_id' => $alcaldia['tenant']->id,
            'contract_id' => $alcaldia['contract']->id,
        ]);

        User::query()->create([
            'name' => 'Supervisor Entidad B',
            'email' => 'supervisor.b@sj-sig.test',
            'password' => $password,
            'role' => UserRole::SupervisorEntidad,
            'tenant_id' => $entidadB['tenant']->id,
            'contract_id' => $entidadB['contract']->id,
        ]);
    }

    /** @return array{tenant: Tenant, contract: Contract} */
    private function seedTenant(string $slug, string $tenantName, string $contractCode, string $contractName, string $city): array
    {
        $tenant = Tenant::query()->create(['name' => $tenantName, 'slug' => $slug]);
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => $contractCode,
            'name' => $contractName,
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
        ]);

        $posts = collect([
            ['code' => 'P01', 'name' => 'Palacio municipal'],
            ['code' => 'P02', 'name' => 'Sede de archivo'],
            ['code' => 'P03', 'name' => 'Centro de convenciones'],
        ])->map(fn (array $row) => Post::query()->create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'code' => $row['code'],
            'name' => $row['name'],
            'city' => $city,
        ]));

        $month = CarbonImmutable::now()->startOfMonth();

        foreach ($posts as $i => $post) {
            ServiceDelivery::query()->create([
                'tenant_id' => $tenant->id,
                'contract_id' => $contract->id,
                'post_id' => $post->id,
                'period_kind' => 'month',
                'period_starts_on' => $month,
                'quantity' => $i === 2 ? 0 : 20 + $i * 4,
            ]);
        }

        $person = Person::query()->create([
            'tenant_id' => $tenant->id,
            'document_type' => 'C',
            'document_number' => $slug === 'alcaldia-a' ? '11440001' : '52220001',
            'full_name' => $slug === 'alcaldia-a' ? 'Ana Vigilante A' : 'Bruno Vigilante B',
            'eps_name' => 'Sura EPS',
            'afp_name' => 'Protección',
            'compensation_fund' => 'Comfandi',
            'hired_on' => '2025-03-01',
        ]);

        $person->contracts()->attach($contract->id, [
            'tenant_id' => $tenant->id,
            'post_id' => $posts->first()->id,
        ]);

        Course::query()->create([
            'tenant_id' => $tenant->id,
            'person_id' => $person->id,
            'title' => 'Manejo de defensas',
            'taken_on' => '2026-02-12',
        ]);

        $relative = 'tenants/'.$tenant->id.'/contracts/'.$contract->id.'/people/'.$person->id.'/hv/hv.txt';
        $absolute = storage_path('app/'.$relative);
        File::ensureDirectoryExists(dirname($absolute));
        File::put($absolute, 'Hoja de vida demo '.$person->full_name);

        PersonDocument::query()->create([
            'tenant_id' => $tenant->id,
            'person_id' => $person->id,
            'folder' => DocumentFolder::HojaVida,
            'original_name' => 'HV.pdf',
            'disk_path' => $relative,
            'mime' => 'text/plain',
            'size_bytes' => 32,
            'expires_on' => now()->addDays(12),
        ]);

        CompanyParafiscal::query()->create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'period' => now()->format('Y-m'),
            'original_name' => 'Planilla PILA '.$month->format('Y-m').'.pdf',
            'disk_path' => 'tenants/'.$tenant->id.'/parafiscal.txt',
        ]);

        $asset = ElectronicAsset::query()->create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'post_id' => $posts->first()->id,
            'kind' => ElectronicAssetKind::Cctv,
            'name' => 'DVR principal',
            'code' => 'CCTV-01',
        ]);

        Maintenance::query()->create([
            'tenant_id' => $tenant->id,
            'electronic_asset_id' => $asset->id,
            'performed_on' => now()->subDays(40),
            'next_due_on' => now()->addDays(5),
            'summary' => 'Limpieza de DVR y prueba de grabación',
            'evidence_path' => 'evidencias/dvr.pdf',
        ]);

        $opener = User::query()->create([
            'name' => 'Sistema seed '.$slug,
            'email' => 'seed.'.$slug.'@sj-sig.test',
            'password' => Hash::make('unused'),
            'role' => UserRole::Operador,
            'tenant_id' => $tenant->id,
        ]);

        Novelty::query()->create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'post_id' => $posts->first()->id,
            'opened_by' => $opener->id,
            'title' => 'Novedad de puesto '.$posts->first()->name,
            'body' => 'Seguimiento de ejecución contractual.',
            'status' => NoveltyStatus::Open,
        ]);

        return compact('tenant', 'contract');
    }
}
