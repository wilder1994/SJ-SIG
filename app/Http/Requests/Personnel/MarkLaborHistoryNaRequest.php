<?php

namespace App\Http\Requests\Personnel;

use App\Enums\LaborHistoryDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MarkLaborHistoryNaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canUploadEvidence() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::enum(LaborHistoryDocumentType::class)],
        ];
    }
}
