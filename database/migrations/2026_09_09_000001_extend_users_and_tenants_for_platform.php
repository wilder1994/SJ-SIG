<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('nit', 32)->nullable()->after('slug');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('document_type', 8)->default('C')->after('name');
            $table->string('document_number', 32)->nullable()->after('document_type');
            $table->string('job_title')->nullable()->after('document_number');
            $table->string('phone', 32)->nullable()->after('job_title');
            $table->string('photo_path')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('contract_id');
            $table->boolean('must_change_password')->default(false)->after('is_active');
        });

        DB::table('users')->where('role', 'operador')->update(['role' => 'interno']);

        $users = DB::table('users')->orderBy('id')->get(['id']);
        foreach ($users as $index => $user) {
            DB::table('users')->where('id', $user->id)->update([
                'document_number' => 'U'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
            ]);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('document_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['document_number']);
            $table->dropColumn([
                'document_type',
                'document_number',
                'job_title',
                'phone',
                'photo_path',
                'is_active',
                'must_change_password',
            ]);
        });

        DB::table('users')->where('role', 'interno')->update(['role' => 'operador']);

        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn('nit');
        });
    }
};
