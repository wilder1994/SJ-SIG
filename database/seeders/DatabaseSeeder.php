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
use App\Support\Files\SimplePdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
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
            'document_type' => 'C',
            'document_number' => '10000001',
            'job_title' => 'Administrador de plataforma',
            'is_active' => true,
            'must_change_password' => false,
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
            'name' => 'Gestión humana SJ',
            'email' => 'interno@sj-sig.test',
            'password' => $password,
            'role' => UserRole::Interno,
            'document_number' => '10000002',
            'job_title' => 'Analista de gestión humana',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        User::query()->create([
            'name' => 'Técnico electrónica',
            'email' => 'tecnico@sj-sig.test',
            'password' => $password,
            'role' => UserRole::TecnicoElectronica,
            'document_number' => '10000003',
            'job_title' => 'Técnico de electrónica',
            'tenant_id' => $alcaldia['tenant']->id,
            'contract_id' => $alcaldia['contract']->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        User::query()->create([
            'name' => 'Supervisor Contratación Pública',
            'email' => 'supervisor.a@sj-sig.test',
            'password' => $password,
            'role' => UserRole::SupervisorEntidad,
            'document_number' => '10000004',
            'job_title' => 'Supervisor de contrato',
            'tenant_id' => $alcaldia['tenant']->id,
            'contract_id' => $alcaldia['contract']->id,
            'is_active' => true,
            'must_change_password' => false,
        ]);

        User::query()->create([
            'name' => 'Supervisor Entidad B',
            'email' => 'supervisor.b@sj-sig.test',
            'password' => $password,
            'role' => UserRole::SupervisorEntidad,
            'document_number' => '10000005',
            'job_title' => 'Supervisor de contrato',
            'tenant_id' => $entidadB['tenant']->id,
            'contract_id' => $entidadB['contract']->id,
            'is_active' => true,
            'must_change_password' => false,
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

        $this->storePersonPdf($tenant->id, $contract->id, $person, DocumentFolder::HojaVida, 'Hoja de vida.pdf', 'Hoja de vida', $person->full_name);
        $this->storePersonPdf($tenant->id, $contract->id, $person, DocumentFolder::Certificados, 'Certificado de aptitud.pdf', 'Certificado de aptitud', 'Vigente para el servicio contratado');
        $this->storePersonPdf($tenant->id, $contract->id, $person, DocumentFolder::Cursos, 'Acta curso manejo de defensas.pdf', 'Acta de curso', 'Manejo de defensas 2026-02-12');
        $this->storePersonPdf($tenant->id, $contract->id, $person, DocumentFolder::Afiliaciones, 'Certificado EPS.pdf', 'Certificado de afiliacion EPS', (string) $person->eps_name);
        $this->storePersonPdf($tenant->id, $contract->id, $person, DocumentFolder::Afiliaciones, 'Certificado pension.pdf', 'Certificado de afiliacion pension', (string) $person->afp_name);
        $this->storePersonPdf($tenant->id, $contract->id, $person, DocumentFolder::Afiliaciones, 'Certificado caja.pdf', 'Certificado de caja de compensacion', (string) $person->compensation_fund);

        $parafiscalRel = 'tenants/'.$tenant->id.'/contracts/'.$contract->id.'/parafiscals/'.$month->format('Y-m').'/pila.pdf';
        SimplePdf::write(storage_path('app/'.$parafiscalRel), 'Planilla PILA '.$month->format('Y-m'), 'Soporte parafiscal de empresa para el contrato '.$contract->code);
        CompanyParafiscal::query()->create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'period' => $month->format('Y-m'),
            'original_name' => 'Planilla PILA '.$month->format('Y-m').'.pdf',
            'disk_path' => $parafiscalRel,
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
            'name' => 'Coordinador operaciones '.$tenantName,
            'email' => $slug === 'alcaldia-a' ? 'ops.a@sj-sig.test' : 'ops.b@sj-sig.test',
            'password' => Hash::make((string) env('SEED_PASSWORD', 'Sig2026!')),
            'role' => UserRole::Operaciones,
            'document_number' => $slug === 'alcaldia-a' ? '20000001' : '20000002',
            'job_title' => 'Coordinador de operaciones',
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'is_active' => true,
            'must_change_password' => false,
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

    private function storePersonPdf(
        int $tenantId,
        int $contractId,
        Person $person,
        DocumentFolder $folder,
        string $filename,
        string $title,
        string $body,
    ): void {
        $relative = sprintf(
            'tenants/%d/contracts/%d/people/%d/%s/%s',
            $tenantId,
            $contractId,
            $person->id,
            $folder->value,
            str_replace(' ', '-', strtolower($filename)),
        );
        $absolute = storage_path('app/'.$relative);
        SimplePdf::write($absolute, $title, $body);
        PersonDocument::query()->create([
            'tenant_id' => $tenantId,
            'person_id' => $person->id,
            'folder' => $folder,
            'original_name' => $filename,
            'disk_path' => $relative,
            'mime' => 'application/pdf',
            'size_bytes' => is_file($absolute) ? (int) filesize($absolute) : 0,
            'expires_on' => $folder === DocumentFolder::HojaVida ? now()->addDays(12) : null,
        ]);
    }
}
