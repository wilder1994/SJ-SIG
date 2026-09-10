<?php

namespace App\Http\Requests\Structure;

use App\Enums\GuardRole;
use App\Enums\ServiceModality;
use App\Http\Requests\Concerns\ValidatesLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSiteRequest extends FormRequest
{
    use ValidatesLocation;

    public function authorize(): bool
    {
        return $this->user()?->role->canManageStructure() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'posts' => ['nullable', 'array'],
            'posts.*.id' => ['nullable', 'integer'],
            'posts.*.name' => ['nullable', 'string', 'max:160'],
            'posts.*.shift_hours' => ['nullable', Rule::enum(ServiceModality::class)],
            'posts.*.staffings' => ['nullable', 'array'],
            'posts.*.staffings.*.role' => ['required_with:posts.*.staffings', Rule::enum(GuardRole::class)],
            'posts.*.staffings.*.slots' => ['required_with:posts.*.staffings', 'integer', 'min:1', 'max:99'],
            'change_reason' => ['nullable', 'string', 'max:2000'],
            ...$this->locationRules(),
        ];
    }
}
