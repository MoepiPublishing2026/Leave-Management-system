<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('leave_requests', 'half_day_period')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->string('half_day_period', 20)->nullable()->after('is_half_day');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leave_requests', 'half_day_period')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->dropColumn('half_day_period');
            });
        }
    }
};
