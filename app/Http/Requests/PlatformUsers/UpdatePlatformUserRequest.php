<?php

namespace App\Http\Requests\PlatformUsers;

use App\Enums\UserRole;
use App\Http\Requests\Concerns\AuthorizesAdmin;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdatePlatformUserRequest extends FormRequest
{
    use AuthorizesAdmin;

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:160'],
            'document_type' => ['required', 'in:C,CE,N,TI,PT'],
            'document_number' => ['required', 'string', 'max:32', Rule::unique('users', 'document_number')->ignore($user)],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:32'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $role = UserRole::tryFrom((string) $this->input('role'));
            if ($role?->requiresClient() && ! $this->filled('tenant_id')) {
                $validator->errors()->add('tenant_id', 'Asigne el cliente.');
            }
        });
    }
}
