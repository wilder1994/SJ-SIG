<?php

namespace App\Http\Requests\Parafiscal;

use Illuminate\Foundation\Http\FormRequest;

final class StoreParafiscalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canUploadEvidence() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'period' => ['required', 'date_format:Y-m'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:12288'],
        ];
    }
}
