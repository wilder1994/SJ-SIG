<?php

namespace App\Http\Requests\Personnel;

use App\Enums\DocumentFolder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePersonDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canUploadEvidence() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'folder' => ['required', Rule::enum(DocumentFolder::class)->except([
                DocumentFolder::HojaVida,
                DocumentFolder::Afiliaciones,
            ])],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:12288'],
            'expires_on' => ['nullable', 'date'],
        ];
    }
}
