<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_requests', 'manager_recommendation')) {
                $table->string('manager_recommendation', 20)->nullable()->after('status');
            }
            if (! Schema::hasColumn('leave_requests', 'manager_recommendation_comment')) {
                $table->text('manager_recommendation_comment')->nullable()->after('manager_recommendation');
            }
            if (! Schema::hasColumn('leave_requests', 'recommended_by')) {
                $table->unsignedBigInteger('recommended_by')->nullable()->after('manager_recommendation_comment');
            }
            if (! Schema::hasColumn('leave_requests', 'recommended_at')) {
                $table->timestamp('recommended_at')->nullable()->after('recommended_by');
            }
            if (! Schema::hasColumn('leave_requests', 'pending_review_reminder_stage')) {
                $table->unsignedTinyInteger('pending_review_reminder_stage')->default(0)->after('recommended_at');
            }
            if (! Schema::hasColumn('leave_requests', 'last_pending_review_reminder_at')) {
                $table->timestamp('last_pending_review_reminder_at')->nullable()->after('pending_review_reminder_stage');
            }
        });

        // Manager applicants skip manager review — mark existing pending manager leave as awaiting senior.
        $managerIds = DB::table('users')->where('role', 'manager')->pluck('id');
        if ($managerIds->isNotEmpty()) {
            DB::table('leave_requests')
                ->whereIn('user_id', $managerIds)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->update(['status' => 'pending_senior']);
        }
    }

    public function down(): void
    {
        DB::table('leave_requests')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending_senior'])
            ->update(['status' => 'pending']);

        Schema::table('leave_requests', function (Blueprint $table) {
            foreach ([
                'manager_recommendation',
                'manager_recommendation_comment',
                'recommended_by',
                'recommended_at',
                'pending_review_reminder_stage',
                'last_pending_review_reminder_at',
            ] as $column) {
                if (Schema::hasColumn('leave_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
