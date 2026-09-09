<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('city')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'code']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->after('password');
            $table->foreignId('tenant_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 8)->default('C');
            $table->string('document_number');
            $table->string('full_name');
            $table->date('birth_date')->nullable();
            $table->string('document_issue_place')->nullable();
            $table->date('document_issued_on')->nullable();
            $table->string('residence_city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('blood_type', 8)->nullable();
            $table->string('sex', 4)->nullable();
            $table->string('education')->nullable();
            $table->string('marital_status')->nullable();
            $table->unsignedTinyInteger('children_count')->nullable();
            $table->string('engagement_type')->nullable();
            $table->string('contributor_type')->nullable();
            $table->date('hired_on')->nullable();
            $table->date('labor_contract_ends_on')->nullable();
            $table->date('left_on')->nullable();
            $table->string('job_code')->nullable();
            $table->string('labor_contract_type')->nullable();
            $table->string('eps_code')->nullable();
            $table->string('eps_name')->nullable();
            $table->string('afp_code')->nullable();
            $table->string('afp_name')->nullable();
            $table->string('arl_name')->nullable();
            $table->string('arl_risk_level')->nullable();
            $table->string('compensation_fund')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'document_number']);
        });

        Schema::create('contract_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['contract_id', 'person_id']);
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('title');
            $table->date('taken_on');
            $table->timestamps();
        });

        Schema::create('person_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('folder', 32);
            $table->string('original_name');
            $table->string('disk_path');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->date('expires_on')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'original_name']);
        });

        Schema::create('company_parafiscals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->char('period', 7);
            $table->string('original_name');
            $table->string('disk_path');
            $table->timestamps();
        });

        Schema::create('electronic_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 32);
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('electronic_asset_id')->constrained()->cascadeOnDelete();
            $table->date('performed_on');
            $table->date('next_due_on')->nullable();
            $table->string('summary');
            $table->string('evidence_path')->nullable();
            $table->timestamps();
        });

        Schema::create('service_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('period_kind', 16);
            $table->date('period_starts_on');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['post_id', 'period_kind', 'period_starts_on'], 'service_deliveries_period_unique');
        });

        Schema::create('novelties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('status', 16);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novelties');
        Schema::dropIfExists('service_deliveries');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('electronic_assets');
        Schema::dropIfExists('company_parafiscals');
        Schema::dropIfExists('person_documents');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('contract_person');
        Schema::dropIfExists('people');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contract_id');
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn('role');
        });
        Schema::dropIfExists('posts');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('tenants');
    }
};
