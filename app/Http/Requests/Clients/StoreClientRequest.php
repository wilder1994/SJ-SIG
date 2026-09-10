<?php

namespace App\Http\Requests\Clients;

use App\Enums\IdDocumentType;
use App\Enums\PersonKind;
use App\Enums\StructureType;
use App\Http\Requests\Concerns\AuthorizesAdmin;
use App\Http\Requests\Concerns\ValidatesLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreClientRequest extends FormRequest
{
    use AuthorizesAdmin;
    use ValidatesLocation;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'person_kind' => ['required', Rule::enum(PersonKind::class)],
            'structure_type' => ['nullable', Rule::enum(StructureType::class)],
            'trade_name' => ['nullable', 'string', 'max:160'],
            'legal_name' => ['required', 'string', 'max:160'],
            'document_type' => ['required', Rule::enum(IdDocumentType::class)],
            'document_number' => ['required', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32'],
            'legal_rep_name' => ['nullable', 'string', 'max:160'],
            'legal_rep_email' => ['nullable', 'email', 'max:160'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            ...$this->locationRules(),
        ];
    }

    /** @return array<string, mixed> */
    public function clientPayload(): array
    {
        $data = $this->validated();

        return [
            'person_kind' => $data['person_kind'],
            'structure_type' => $data['structure_type'] ?? null,
            'trade_name' => $data['trade_name'] ?? null,
            'legal_name' => $data['legal_name'],
            'name' => $data['legal_name'],
            'document_type' => $data['document_type'],
            'nit' => $data['document_number'],
            'contact_email' => $data['contact_email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'legal_rep_name' => $data['legal_rep_name'] ?? null,
            'legal_rep_email' => $data['legal_rep_email'] ?? null,
            'starts_on' => $data['starts_on'] ?? null,
            'ends_on' => $data['ends_on'] ?? null,
            ...$this->locationPayload($data),
        ];
    }
}
