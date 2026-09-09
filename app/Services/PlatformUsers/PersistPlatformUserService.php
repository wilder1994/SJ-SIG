<?php

namespace App\Services\PlatformUsers;

use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class PersistPlatformUserService
{
    /** @param array<string, mixed> $payload */
    public function create(array $payload, ?UploadedFile $photo): User
    {
        $role = $this->role($payload['role']);
        $assignment = $this->assignment($role, $payload['tenant_id'] ?? null);

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'role' => $role,
            'document_type' => $payload['document_type'],
            'document_number' => $payload['document_number'],
            'job_title' => $payload['job_title'] ?: null,
            'phone' => $payload['phone'] ?: null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'must_change_password' => true,
            'tenant_id' => $assignment['tenant_id'],
            'contract_id' => $assignment['contract_id'],
        ]);

        if ($photo !== null) {
            $user->photo_path = $this->storePhoto($user, $photo);
            $user->save();
        }

        return $user;
    }

    /** @param array<string, mixed> $payload */
    public function update(User $user, array $payload, ?UploadedFile $photo): User
    {
        $role = $this->role($payload['role']);
        $assignment = $this->assignment($role, $payload['tenant_id'] ?? null);

        $user->fill([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'role' => $role,
            'document_type' => $payload['document_type'],
            'document_number' => $payload['document_number'],
            'job_title' => $payload['job_title'] ?: null,
            'phone' => $payload['phone'] ?: null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'tenant_id' => $assignment['tenant_id'],
            'contract_id' => $assignment['contract_id'],
        ]);

        if (! empty($payload['password'])) {
            $user->password = Hash::make($payload['password']);
            $user->must_change_password = true;
        }

        if ($photo !== null) {
            $user->photo_path = $this->storePhoto($user, $photo);
        }

        $user->save();

        return $user;
    }

    public function changePassword(User $user, string $password): void
    {
        $user->password = Hash::make($password);
        $user->must_change_password = false;
        $user->save();
    }

    private function role(mixed $value): UserRole
    {
        return $value instanceof UserRole ? $value : UserRole::from((string) $value);
    }

    /** @return array{tenant_id: int|null, contract_id: int|null} */
    private function assignment(UserRole $role, mixed $tenantId): array
    {
        if ($role->seesAllClients()) {
            return ['tenant_id' => null, 'contract_id' => null];
        }

        $id = (int) $tenantId;
        $contract = Contract::query()->where('tenant_id', $id)->orderBy('id')->first();

        return [
            'tenant_id' => $id,
            'contract_id' => $contract?->id,
        ];
    }

    private function storePhoto(User $user, UploadedFile $photo): string
    {
        $dir = storage_path('app/avatars');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $name = $user->id.'-'.Str::random(8).'.'.strtolower($photo->getClientOriginalExtension() ?: 'jpg');
        $photo->move($dir, $name);

        return 'avatars/'.$name;
    }
}
