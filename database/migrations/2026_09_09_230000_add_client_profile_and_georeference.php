<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('person_kind', 16)->default('juridica')->after('slug');
            $table->string('structure_type', 32)->nullable()->after('person_kind');
            $table->string('trade_name')->nullable()->after('name');
            $table->string('legal_name')->nullable()->after('trade_name');
            $table->string('document_type', 8)->default('NIT')->after('legal_name');
            $table->string('contact_email')->nullable()->after('nit');
            $table->string('phone', 32)->nullable()->after('contact_email');
            $table->string('legal_rep_name')->nullable()->after('phone');
            $table->string('legal_rep_email')->nullable()->after('legal_rep_name');
            $table->string('address')->nullable()->after('legal_rep_email');
            $table->string('city')->nullable()->after('address');
            $table->string('department')->nullable()->after('city');
            $table->decimal('lat', 10, 7)->nullable()->after('department');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('place_id')->nullable()->after('lng');
        });

        Schema::table('sites', function (Blueprint $table): void {
            $table->string('address')->nullable()->after('city');
            $table->string('department')->nullable()->after('address');
            $table->decimal('lat', 10, 7)->nullable()->after('department');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('place_id')->nullable()->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn([
                'person_kind',
                'structure_type',
                'trade_name',
                'legal_name',
                'document_type',
                'contact_email',
                'phone',
                'legal_rep_name',
                'legal_rep_email',
                'address',
                'city',
                'department',
                'lat',
                'lng',
                'place_id',
            ]);
        });

        Schema::table('sites', function (Blueprint $table): void {
            $table->dropColumn(['address', 'department', 'lat', 'lng', 'place_id']);
        });
    }
};
