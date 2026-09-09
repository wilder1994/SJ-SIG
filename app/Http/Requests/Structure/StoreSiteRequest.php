<?php

namespace App\Http\Requests\Structure;

use App\Enums\ServiceModality;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canManageStructure() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $contractId = $this->attributes->get('currentContract')?->id;

        return [
            'code' => ['required', 'string', 'max:16', Rule::unique('sites', 'code')->where('contract_id', $contractId)],
            'name' => ['required', 'string', 'max:160'],
            'city' => ['nullable', 'string', 'max:80'],
        ];
    }
}
