<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('person_documents')
            ->whereIn('document_type', [
                'examen_medico_ingreso',
                'examen_psicofisico',
                'examen_psicosensometrico',
            ])
            ->update(['folder' => 'certificados']);
    }

    public function down(): void
    {
        DB::table('person_documents')
            ->whereIn('document_type', [
                'examen_medico_ingreso',
                'examen_psicofisico',
                'examen_psicosensometrico',
            ])
            ->update(['folder' => 'hv']);
    }
};
