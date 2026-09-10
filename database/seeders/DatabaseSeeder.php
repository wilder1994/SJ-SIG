<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@sj-sig.test');
        $password = (string) env('ADMIN_PASSWORD', env('SEED_PASSWORD', 'Sig2026!'));

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('ADMIN_NAME', 'Administración SJ'),
                'password' => Hash::make($password),
                'role' => UserRole::AdminEmpresa,
                'document_type' => 'C',
                'document_number' => '10000001',
                'job_title' => 'Administrador de plataforma',
                'tenant_id' => null,
                'contract_id' => null,
                'is_active' => true,
                'must_change_password' => false,
            ],
        );
    }
}
