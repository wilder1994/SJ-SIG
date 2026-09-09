<?php

namespace App\Http\Requests\Structure;

use App\Enums\ServiceModality;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePostRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:16', Rule::unique('posts', 'code')->where('contract_id', $contractId)],
            'name' => ['required', 'string', 'max:160'],
            'shift_hours' => ['required', Rule::enum(ServiceModality::class)],
            'guard_slots' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}
