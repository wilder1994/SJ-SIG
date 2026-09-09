<?php

namespace App\Http\Requests\Personnel;

use App\Enums\DocumentFolder;
use App\Models\DocumentBatch;
use App\Support\Personnel\IndexedFolder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

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
            'slices.*.document_type' => ['required', 'string'],
            'slices.*.display_name' => ['required', 'string', 'max:180'],
            'slices.*.pages' => ['required', 'array', 'min:1'],
            'slices.*.pages.*' => ['required', 'integer', 'min:1'],
            'slices.*.taken_on' => ['nullable', 'date'],
            'slices.*.provider' => ['nullable', 'string', 'max:180'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator): void {
            $batch = DocumentBatch::query()->find((int) $this->route('batch'));
            $folder = $batch?->folder;
            foreach ($this->input('slices', []) as $index => $slice) {
                $pages = array_map('intval', $slice['pages'] ?? []);
                if ($pages !== array_values(array_unique($pages))) {
                    $validator->errors()->add('slices.'.$index.'.pages', 'Hay páginas repetidas en este corte.');
                }
                if ($folder !== null && isset($slice['document_type'])) {
                    try {
                        IndexedFolder::resolve($folder, (string) $slice['document_type']);
                    } catch (InvalidArgumentException) {
                        $validator->errors()->add('slices.'.$index.'.document_type', 'Tipo no válido para esta carpeta.');
                    }
                }
                if ($folder === DocumentFolder::Cursos) {
                    if (blank($slice['taken_on'] ?? null)) {
                        $validator->errors()->add('slices.'.$index.'.taken_on', 'La fecha del curso es obligatoria.');
                    }
                    if (blank($slice['provider'] ?? null)) {
                        $validator->errors()->add('slices.'.$index.'.provider', 'La entidad que dicta el curso es obligatoria.');
                    }
                }
            }
        });
    }
}
