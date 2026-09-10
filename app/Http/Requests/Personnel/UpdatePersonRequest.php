<?php

namespace App\Http\Requests\Personnel;

use App\Models\Contract;
use Illuminate\Validation\Rule;

final class UpdatePersonRequest extends StorePersonRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = parent::rules();
        $contract = $this->attributes->get('currentContract');
        $tenantId = $contract instanceof Contract ? $contract->tenant_id : null;
        $personId = (int) $this->route('person');

        $rules['document_number'] = [
            'required',
            'string',
            'max:32',
            Rule::unique('people', 'document_number')
                ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                ->ignore($personId),
        ];

        return $rules;
    }
}
