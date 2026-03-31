<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_request_conversations') && !Schema::hasColumn('leave_request_conversations', 'admin_id')) {
            Schema::table('leave_request_conversations', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('manager_id');
                $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (Schema::hasTable('leave_request_messages')) {
            Schema::table('leave_request_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('leave_request_messages', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('body');
                }
                if (!Schema::hasColumn('leave_request_messages', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('delivered_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leave_request_messages')) {
            Schema::table('leave_request_messages', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('leave_request_messages', 'delivered_at')) {
                    $columns[] = 'delivered_at';
                }
                if (Schema::hasColumn('leave_request_messages', 'read_at')) {
                    $columns[] = 'read_at';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('leave_request_conversations') && Schema::hasColumn('leave_request_conversations', 'admin_id')) {
            Schema::table('leave_request_conversations', function (Blueprint $table) {
                $table->dropForeign(['admin_id']);
                $table->dropColumn(['admin_id']);
            });
        }
    }
};

