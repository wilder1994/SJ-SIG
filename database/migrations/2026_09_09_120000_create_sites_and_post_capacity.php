<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('city')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'code']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable()->after('contract_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('shift_hours')->default(12)->after('city');
            $table->unsignedInteger('guard_slots')->default(1)->after('shift_hours');
        });

        $contracts = DB::table('contracts')->get(['id', 'tenant_id']);
        foreach ($contracts as $contract) {
            $siteId = DB::table('sites')->insertGetId([
                'tenant_id' => $contract->tenant_id,
                'contract_id' => $contract->id,
                'code' => 'S01',
                'name' => 'Instalación principal',
                'city' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('posts')->where('contract_id', $contract->id)->update(['site_id' => $siteId]);
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn(['shift_hours', 'guard_slots']);
        });
        Schema::dropIfExists('sites');
    }
};
