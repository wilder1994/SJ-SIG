<?php

namespace App\Http\Requests\Structure;

use App\Http\Requests\Concerns\ValidatesLocation;
use Illuminate\Foundation\Http\FormRequest;

final class StoreSiteRequest extends FormRequest
{
    use ValidatesLocation;

    public function authorize(): bool
    {
        return $this->user()?->role->canManageStructure() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            ...$this->locationRules(),
        ];
    }
}
