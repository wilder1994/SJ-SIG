<?php

namespace App\Services\Personnel;

use App\Models\Contract;
use App\Models\Person;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

final class ImportPersonnelWorkbookService
{
    /** @var array<string, string> */
    private const LABELS = [
        'document_type' => 'Tipo de documento',
        'document_number' => 'Cédula',
        'full_name' => 'Nombre',
        'birth_date' => 'Fecha de nacimiento',
        'document_issue_place' => 'Lugar de expedición',
        'document_issued_on' => 'Fecha de expedición',
        'residence_city' => 'Lugar de residencia',
        'address' => 'Dirección',
        'phone' => 'Teléfono',
        'email' => 'Correo',
        'blood_type' => 'Tipo de sangre',
        'sex' => 'Sexo',
        'education' => 'Escolaridad',
        'marital_status' => 'Estado civil',
        'children_count' => 'Número de hijos',
        'engagement_type' => 'Tipo de vinculación',
        'contributor_type' => 'Tipo de cotizante',
        'hired_on' => 'Fecha de ingreso',
        'labor_contract_ends_on' => 'Vencimiento de contrato',
        'left_on' => 'Fecha de retiro',
        'job_code' => 'Cargo',
        'labor_contract_type' => 'Tipo de contrato',
        'eps_code' => 'Código EPS',
        'eps_name' => 'EPS',
        'afp_code' => 'Código AFP',
        'afp_name' => 'AFP',
        'arl_name' => 'ARL',
        'arl_risk_level' => 'Nivel de riesgo ARL',
        'compensation_fund' => 'Caja de compensación',
    ];

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     unchanged: int,
     *     errors: int,
     *     valid: int,
     *     rows: list<array{
     *         row: int,
     *         status: 'create'|'update'|'same'|'error',
     *         document_number: string,
     *         full_name: string,
     *         message: ?string,
     *         warnings: list<string>,
     *         changes: list<array{field: string, label: string, from: string, to: string}>,
     *         payload: ?array<string, mixed>
     *     }>
     * }
     */
    public function preview(Contract $contract, string $absolutePath): array
    {
        return $this->analyze($contract, $absolutePath);
    }

    /**
     * @return array{created: int, updated: int, unchanged: int, errors: list<array{row: int, message: string}>}
     */
    public function commit(Contract $contract, string $absolutePath): array
    {
        $analysis = $this->analyze($contract, $absolutePath);

        foreach ($analysis['rows'] as $row) {
            if ($row['payload'] === null || $row['status'] === 'error') {
                continue;
            }

            $this->persist($contract, $row['payload']);
        }

        $errorLog = [];
        foreach ($analysis['rows'] as $row) {
            if ($row['status'] === 'error') {
                $errorLog[] = ['row' => $row['row'], 'message' => (string) $row['message']];
            }
        }

        return [
            'created' => $analysis['created'],
            'updated' => $analysis['updated'],
            'unchanged' => $analysis['unchanged'],
            'errors' => $errorLog,
        ];
    }

    /**
     * @return array{created: int, updated: int, errors: list<array{row: int, message: string}>}
     */
    public function execute(Contract $contract, string $absolutePath): array
    {
        $result = $this->commit($contract, $absolutePath);

        return [
            'created' => $result['created'],
            'updated' => $result['updated'] + $result['unchanged'],
            'errors' => $result['errors'],
        ];
    }

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     unchanged: int,
     *     errors: int,
     *     valid: int,
     *     rows: list<array{
     *         row: int,
     *         status: 'create'|'update'|'same'|'error',
     *         document_number: string,
     *         full_name: string,
     *         message: ?string,
     *         warnings: list<string>,
     *         changes: list<array{field: string, label: string, from: string, to: string}>,
     *         payload: ?array<string, mixed>
     *     }>
     * }
     */
    private function analyze(Contract $contract, string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException('El archivo de importación no existe.');
        }

        try {
            $sheet = IOFactory::load($absolutePath)->getActiveSheet();
        } catch (\Throwable) {
            throw new RuntimeException('No se pudo leer el Excel. Use la plantilla SJ-SIG (.xlsx).');
        }

        $headerMap = [];
        $seen = [];
        $rows = [];
        $created = 0;
        $updated = 0;
        $unchanged = 0;
        $errors = 0;

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

                if (! isset($headerMap['cedula'])) {
                    throw new RuntimeException('El archivo no tiene la columna cedula. Use la plantilla SJ-SIG.');
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

                $rows[] = $this->issue($index, $cedula, $this->col($values, $headerMap, 'nombre'), 'Cédula vacía.');
                $errors++;

                continue;
            }

            if (isset($seen[$cedula])) {
                $rows[] = $this->issue($index, $cedula, $this->col($values, $headerMap, 'nombre'), 'Cédula repetida en el archivo (fila '.$seen[$cedula].').');
                $errors++;

                continue;
            }

            $seen[$cedula] = $index;

            $dates = [
                'fecha_nac' => 'Fecha de nacimiento',
                'fecha_expedicion' => 'Fecha de expedición',
                'fecha_ingreso' => 'Fecha de ingreso',
                'fecha_vencimiento_contrato' => 'Vencimiento de contrato',
                'fecha_retiro' => 'Fecha de retiro',
            ];
            $warnings = [];
            foreach ($dates as $key => $label) {
                $raw = $this->col($values, $headerMap, $key);
                if ($raw !== '' && $this->dateOrNull($raw) === null) {
                    $warnings[] = $label.' no se entendió y quedará vacía.';
                }
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
                $rows[] = $this->issue($index, $cedula, '', 'Nombre vacío.');
                $errors++;

                continue;
            }

            $existing = Person::query()
                ->where('tenant_id', $contract->tenant_id)
                ->where('document_number', $cedula)
                ->first();

            if ($existing === null) {
                $created++;
                $rows[] = [
                    'row' => $index,
                    'status' => 'create',
                    'document_number' => $cedula,
                    'full_name' => $payload['full_name'],
                    'message' => 'Alta nueva en este cliente.',
                    'warnings' => $warnings,
                    'changes' => [],
                    'payload' => $payload,
                ];

                continue;
            }

            $changes = $this->diff($existing, $payload);
            $onContract = $existing->contracts()->where('contracts.id', $contract->id)->exists();
            $status = $changes === [] ? 'same' : 'update';

            if ($status === 'update') {
                $updated++;
                $message = $onContract
                    ? 'Se actualizarán datos de la ficha.'
                    : 'Ya existe en el cliente; se actualiza y se asigna a este contrato.';
            } else {
                $unchanged++;
                $message = $onContract
                    ? 'Sin cambios en la ficha.'
                    : 'Sin cambios en la ficha; se confirma en este contrato.';
            }

            $rows[] = [
                'row' => $index,
                'status' => $status,
                'document_number' => $cedula,
                'full_name' => $payload['full_name'],
                'message' => $message,
                'warnings' => $warnings,
                'changes' => $changes,
                'payload' => $payload,
            ];
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'unchanged' => $unchanged,
            'errors' => $errors,
            'valid' => $created + $updated + $unchanged,
            'rows' => $rows,
        ];
    }

    /** @param array<string, mixed> $payload */
    private function persist(Contract $contract, array $payload): void
    {
        DB::transaction(function () use ($contract, $payload): void {
            $person = Person::query()->updateOrCreate(
                [
                    'tenant_id' => $contract->tenant_id,
                    'document_number' => $payload['document_number'],
                ],
                $payload,
            );

            $postId = DB::table('contract_person')
                ->where('person_id', $person->id)
                ->where('contract_id', $contract->id)
                ->value('post_id');

            $person->contracts()->syncWithoutDetaching([
                $contract->id => [
                    'tenant_id' => $contract->tenant_id,
                    'post_id' => $postId,
                ],
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{field: string, label: string, from: string, to: string}>
     */
    private function diff(Person $person, array $payload): array
    {
        $changes = [];

        foreach (self::LABELS as $field => $label) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $from = $this->display($person->{$field});
            $to = $this->display($payload[$field]);
            if ($from === $to) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => $label,
                'from' => $from === '' ? '—' : $from,
                'to' => $to === '' ? '—' : $to,
            ];
        }

        return $changes;
    }

    /**
     * @return array{
     *     row: int,
     *     status: 'error',
     *     document_number: string,
     *     full_name: string,
     *     message: string,
     *     warnings: list<string>,
     *     changes: list<array{field: string, label: string, from: string, to: string}>,
     *     payload: null
     * }
     */
    private function issue(int $row, string $cedula, string $name, string $message): array
    {
        return [
            'row' => $row,
            'status' => 'error',
            'document_number' => $cedula,
            'full_name' => $name,
            'message' => $message,
            'warnings' => [],
            'changes' => [],
            'payload' => null,
        ];
    }

    private function display(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
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
