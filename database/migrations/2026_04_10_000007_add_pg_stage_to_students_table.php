<?php

use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('pg_stage', 10)
                ->default(Student::PG_STAGE_PG1)
                ->after('semester');
        });

        DB::table('students')
            ->whereNull('pg_stage')
            ->update(['pg_stage' => Student::PG_STAGE_PG1]);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('pg_stage');
        });
    }
};
