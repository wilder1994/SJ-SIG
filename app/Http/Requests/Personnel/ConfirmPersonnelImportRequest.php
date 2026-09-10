<?php

namespace App\Http\Requests\Personnel;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmPersonnelImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canImportPersonnel() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'uuid'],
        ];
    }
}
