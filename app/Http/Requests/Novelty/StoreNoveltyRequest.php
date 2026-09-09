<?php

namespace App\Http\Requests\Novelty;

use Illuminate\Foundation\Http\FormRequest;

final class StoreNoveltyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role->canMutateNovelties() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:4000'],
            'post_id' => ['nullable', 'integer'],
        ];
    }
}
