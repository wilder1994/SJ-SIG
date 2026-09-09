<?php

namespace App\Http\Requests\Personnel;

use App\Enums\LaborHistoryDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class IndexLaborHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canUploadEvidence() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'slices' => ['required', 'array', 'min:1'],
            'slices.*.document_type' => ['required', Rule::enum(LaborHistoryDocumentType::class)],
            'slices.*.display_name' => ['required', 'string', 'max:180'],
            'slices.*.page_from' => ['required', 'integer', 'min:1'],
            'slices.*.page_to' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            foreach ($this->input('slices', []) as $index => $slice) {
                $from = (int) ($slice['page_from'] ?? 0);
                $to = (int) ($slice['page_to'] ?? 0);
                if ($from > 0 && $to > 0 && $to < $from) {
                    $validator->errors()->add('slices.'.$index.'.page_to', 'La página final debe ser mayor o igual que la inicial.');
                }
            }
        });
    }
}
