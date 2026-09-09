<?php

namespace App\Http\Requests\Clients;

use App\Http\Requests\Concerns\AuthorizesAdmin;
use Illuminate\Foundation\Http\FormRequest;

final class StoreClientRequest extends FormRequest
{
    use AuthorizesAdmin;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'nit' => ['nullable', 'string', 'max:32'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ];
    }
}
