<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('disk_path');
            $table->string('mime', 120)->nullable();
            $table->unsignedInteger('page_count')->default(1);
            $table->timestamps();
        });

        Schema::table('person_documents', function (Blueprint $table): void {
            $table->string('document_type', 64)->nullable()->after('folder');
            $table->string('display_name')->nullable()->after('document_type');
            $table->unsignedSmallInteger('page_from')->nullable()->after('display_name');
            $table->unsignedSmallInteger('page_to')->nullable()->after('page_from');
            $table->boolean('not_applicable')->default(false)->after('page_to');
        });
    }

    public function down(): void
    {
        Schema::table('person_documents', function (Blueprint $table): void {
            $table->dropColumn([
                'document_type',
                'display_name',
                'page_from',
                'page_to',
                'not_applicable',
            ]);
        });
        Schema::dropIfExists('document_batches');
    }
};
