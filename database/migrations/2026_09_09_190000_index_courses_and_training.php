<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('provider', 180)->nullable()->after('title');
            $table->string('course_type', 64)->nullable()->after('provider');
            $table->foreignId('person_document_id')->nullable()->after('course_type')->constrained('person_documents')->nullOnDelete();
        });

        Schema::table('person_documents', function (Blueprint $table): void {
            $table->date('taken_on')->nullable()->after('expires_on');
            $table->string('provider', 180)->nullable()->after('taken_on');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('person_document_id');
            $table->dropColumn(['provider', 'course_type']);
        });

        Schema::table('person_documents', function (Blueprint $table): void {
            $table->dropColumn(['taken_on', 'provider']);
        });
    }
};
