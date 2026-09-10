<?php

use App\Enums\GuardRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_staffings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('role', 40);
            $table->unsignedInteger('slots');
            $table->timestamps();

            $table->unique(['post_id', 'role']);
        });

        Schema::create('site_service_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 16);
            $table->date('effective_on');
            $table->date('last_shift_on')->nullable();
            $table->string('requested_by')->nullable();
            $table->text('reason')->nullable();
            $table->string('from_summary')->nullable();
            $table->string('to_summary')->nullable();
            $table->json('snapshot')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $posts = DB::table('posts')->get(['id', 'tenant_id', 'contract_id', 'guard_slots']);
        foreach ($posts as $post) {
            $slots = max(1, (int) $post->guard_slots);
            DB::table('post_staffings')->insert([
                'tenant_id' => $post->tenant_id,
                'contract_id' => $post->contract_id,
                'post_id' => $post->id,
                'role' => GuardRole::Vigilante->value,
                'slots' => $slots,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_service_events');
        Schema::dropIfExists('post_staffings');
    }
};
