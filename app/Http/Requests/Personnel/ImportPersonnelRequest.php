<?php

namespace App\Http\Requests\Personnel;

use Illuminate\Foundation\Http\FormRequest;

final class ImportPersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canImportPersonnel() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'workbook' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ];
    }
}
