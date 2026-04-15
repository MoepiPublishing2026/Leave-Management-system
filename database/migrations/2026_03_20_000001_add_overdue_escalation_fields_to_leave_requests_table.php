<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leave_requests', 'overdue_escalation_reason')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->text('overdue_escalation_reason')->nullable()->after('rejection_reason');
            });
        }
        if (!Schema::hasColumn('leave_requests', 'overdue_escalated_at')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->timestamp('overdue_escalated_at')->nullable()->after('overdue_escalation_reason');
            });
        }
        if (!Schema::hasColumn('leave_requests', 'overdue_escalated_by')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('overdue_escalated_by')->nullable()->after('overdue_escalated_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['overdue_escalation_reason', 'overdue_escalated_at', 'overdue_escalated_by']);
        });
    }
};
