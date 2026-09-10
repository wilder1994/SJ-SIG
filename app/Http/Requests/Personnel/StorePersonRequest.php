<?php

namespace App\Http\Requests\Personnel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonRequest extends FormRequest
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
            'birth_date' => ['nullable', 'date'],
            'document_issue_place' => ['nullable', 'string', 'max:120'],
            'document_issued_on' => ['nullable', 'date'],
            'residence_city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email'],
            'blood_type' => ['nullable', 'string', 'max:16'],
            'sex' => ['nullable', 'string', 'max:32'],
            'education' => ['nullable', 'string', 'max:80'],
            'marital_status' => ['nullable', 'string', 'max:80'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:30'],
            'engagement_type' => ['nullable', 'string', 'max:80'],
            'contributor_type' => ['nullable', 'string', 'max:80'],
            'hired_on' => ['nullable', 'date'],
            'labor_contract_ends_on' => ['nullable', 'date'],
            'left_on' => ['nullable', 'date'],
            'job_code' => ['nullable', 'string', 'max:64'],
            'labor_contract_type' => ['nullable', 'string', 'max:80'],
            'eps_code' => ['nullable', 'string', 'max:32'],
            'eps_name' => ['nullable', 'string', 'max:120'],
            'afp_code' => ['nullable', 'string', 'max:32'],
            'afp_name' => ['nullable', 'string', 'max:120'],
            'arl_name' => ['nullable', 'string', 'max:120'],
            'arl_risk_level' => ['nullable', 'string', 'max:32'],
            'compensation_fund' => ['nullable', 'string', 'max:120'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** @return array<string, mixed> */
    public function personPayload(): array
    {
        $data = $this->safe()->except(['photo']);
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
