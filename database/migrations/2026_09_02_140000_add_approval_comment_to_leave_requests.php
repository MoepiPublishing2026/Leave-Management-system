<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('leave_requests', 'approval_comment')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->text('approval_comment')->nullable()->after('rejection_reason');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leave_requests', 'approval_comment')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('approval_comment');
            });
        }
    }
};
