<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('proposal_academic_period_id')->nullable()->after('project_status_id')->constrained('academic_periods')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('assignment_academic_period_id')->nullable()->after('proposal_academic_period_id')->constrained('academic_periods')->nullOnDelete()->cascadeOnUpdate();
            $table->dateTime('proposed_at')->nullable()->after('assignment_academic_period_id');
            $table->dateTime('assigned_at')->nullable()->after('proposed_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proposal_academic_period_id');
            $table->dropConstrainedForeignId('assignment_academic_period_id');
            $table->dropColumn(['proposed_at', 'assigned_at']);
        });
    }
};
