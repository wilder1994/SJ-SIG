<?php

namespace App\Services\Personnel;

use App\Models\Contract;
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

final class ImportPersonnelWorkbookService
{
    /**
     * @return array{created: int, updated: int, errors: list<array{row: int, message: string}>}
     */
    public function execute(Contract $contract, string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException('El archivo de importación no existe.');
        }

        $sheet = IOFactory::load($absolutePath)->getActiveSheet();
        $headerMap = [];
        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($sheet->getRowIterator() as $row) {
            $index = $row->getRowIndex();
            $values = [];
            foreach ($row->getCellIterator('A', 'AC') as $cell) {
                $values[] = trim((string) $cell->getFormattedValue());
            }

            if ($index === 1) {
                foreach ($values as $i => $key) {
                    if ($key !== '') {
                        $headerMap[$key] = $i;
                    }
                }

                continue;
            }

            if ($index === 2) {
                continue;
            }

            $cedula = $this->col($values, $headerMap, 'cedula');
            if ($cedula === '') {
                if ($this->rowIsEmpty($values)) {
                    continue;
                }
                $errors[] = ['row' => $index, 'message' => 'Cédula vacía.'];

                continue;
            }

            $payload = [
                'tenant_id' => $contract->tenant_id,
                'document_type' => $this->col($values, $headerMap, 'tipo_documento') ?: 'C',
                'document_number' => $cedula,
                'full_name' => $this->col($values, $headerMap, 'nombre'),
                'birth_date' => $this->dateOrNull($this->col($values, $headerMap, 'fecha_nac')),
                'document_issue_place' => $this->col($values, $headerMap, 'lugar_exp_cedula') ?: null,
                'document_issued_on' => $this->dateOrNull($this->col($values, $headerMap, 'fecha_expedicion')),
                'residence_city' => $this->col($values, $headerMap, 'lugar_residencia') ?: null,
                'address' => $this->col($values, $headerMap, 'direccion') ?: null,
                'phone' => $this->col($values, $headerMap, 'telefono') ?: null,
                'email' => $this->col($values, $headerMap, 'email') ?: null,
                'blood_type' => $this->col($values, $headerMap, 'tipo_sangre') ?: null,
                'sex' => $this->col($values, $headerMap, 'sexo') ?: null,
                'education' => $this->col($values, $headerMap, 'escolaridad') ?: null,
                'marital_status' => $this->col($values, $headerMap, 'estado_civil') ?: null,
                'children_count' => $this->intOrNull($this->col($values, $headerMap, 'numero_hijos')),
                'engagement_type' => $this->col($values, $headerMap, 'tipo_vinculacion') ?: null,
                'contributor_type' => $this->col($values, $headerMap, 'tipo_cotizante') ?: null,
                'hired_on' => $this->dateOrNull($this->col($values, $headerMap, 'fecha_ingreso')),
                'labor_contract_ends_on' => $this->dateOrNull($this->col($values, $headerMap, 'fecha_vencimiento_contrato')),
                'left_on' => $this->dateOrNull($this->col($values, $headerMap, 'fecha_retiro')),
                'job_code' => $this->col($values, $headerMap, 'cargo') ?: null,
                'labor_contract_type' => $this->col($values, $headerMap, 'tipo_contrato') ?: null,
                'eps_code' => $this->col($values, $headerMap, 'codigo_eps') ?: null,
                'eps_name' => $this->col($values, $headerMap, 'nombre_eps') ?: null,
                'afp_code' => $this->col($values, $headerMap, 'codigo_afp') ?: null,
                'afp_name' => $this->col($values, $headerMap, 'nombre_afp') ?: null,
                'arl_name' => $this->col($values, $headerMap, 'nombre_arp') ?: null,
                'arl_risk_level' => $this->col($values, $headerMap, 'nivel_riesgo_arp') ?: null,
                'compensation_fund' => $this->col($values, $headerMap, 'nombre_caja_compensacion') ?: null,
            ];

            if ($payload['full_name'] === '') {
                $errors[] = ['row' => $index, 'message' => 'Nombre vacío.'];

                continue;
            }

            DB::transaction(function () use ($contract, $payload, &$created, &$updated): void {
                $person = Person::query()->updateOrCreate(
                    [
                        'tenant_id' => $contract->tenant_id,
                        'document_number' => $payload['document_number'],
                    ],
                    $payload,
                );

                $attached = $person->wasRecentlyCreated;
                $person->contracts()->syncWithoutDetaching([
                    $contract->id => [
                        'tenant_id' => $contract->tenant_id,
                        'post_id' => null,
                    ],
                ]);

                if ($attached) {
                    $created++;
                } else {
                    $updated++;
                }
            });
        }

        return compact('created', 'updated', 'errors');
    }

    /**
     * @param  list<string>  $values
     * @param  array<string, int>  $headerMap
     */
    private function col(array $values, array $headerMap, string $key): string
    {
        $i = $headerMap[$key] ?? null;

        return $i === null ? '' : ($values[$i] ?? '');
    }

    /** @param  list<string>  $values */
    private function rowIsEmpty(array $values): bool
    {
        return implode('', $values) === '';
    }

    private function dateOrNull(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $parsed = date_create($value);

        return $parsed === false ? null : $parsed->format('Y-m-d');
    }

    private function intOrNull(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        return (int) $value;
    }
}
