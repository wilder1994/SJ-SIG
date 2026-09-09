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
            'slices.*.pages' => ['required', 'array', 'min:1'],
            'slices.*.pages.*' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            foreach ($this->input('slices', []) as $index => $slice) {
                $pages = array_map('intval', $slice['pages'] ?? []);
                if ($pages !== array_values(array_unique($pages))) {
                    $validator->errors()->add('slices.'.$index.'.pages', 'Hay páginas repetidas en este corte.');
                }
            }
        });
    }
}
