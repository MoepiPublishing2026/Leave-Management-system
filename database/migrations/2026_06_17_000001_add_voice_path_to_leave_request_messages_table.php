<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_request_messages')) {
            return;
        }

        Schema::table('leave_request_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_request_messages', 'voice_path')) {
                $table->string('voice_path')->nullable()->after('body');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('leave_request_messages')) {
            return;
        }

        Schema::table('leave_request_messages', function (Blueprint $table) {
            if (Schema::hasColumn('leave_request_messages', 'voice_path')) {
                $table->dropColumn('voice_path');
            }
        });
    }
};
