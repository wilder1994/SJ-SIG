<?php

namespace App\Http\Requests\Personnel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canUploadEvidence() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', Rule::in(['C', 'CE', 'N', 'TI', 'PT'])],
            'document_number' => ['required', 'string', 'max:32'],
            'full_name' => ['required', 'string', 'max:180'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'eps_name' => ['nullable', 'string', 'max:120'],
            'afp_name' => ['nullable', 'string', 'max:120'],
            'compensation_fund' => ['nullable', 'string', 'max:120'],
            'arl_name' => ['nullable', 'string', 'max:120'],
            'job_code' => ['nullable', 'string', 'max:64'],
            'hired_on' => ['nullable', 'date'],
        ];
    }

    /** @return array<string, mixed> */
    public function personPayload(): array
    {
        return $this->safe()->except([]);
    }
}
