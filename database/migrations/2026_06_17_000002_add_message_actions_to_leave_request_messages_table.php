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
            if (!Schema::hasColumn('leave_request_messages', 'reply_to_message_id')) {
                $table->unsignedBigInteger('reply_to_message_id')->nullable()->after('sender_id');
            }
            if (!Schema::hasColumn('leave_request_messages', 'deleted_for_everyone_at')) {
                $table->timestamp('deleted_for_everyone_at')->nullable()->after('read_at');
            }
        });

        if (!Schema::hasTable('leave_request_message_hidden')) {
            Schema::create('leave_request_message_hidden', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->unique(['message_id', 'user_id']);
                $table->foreign('message_id')->references('id')->on('leave_request_messages')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_message_hidden');

        if (!Schema::hasTable('leave_request_messages')) {
            return;
        }

        Schema::table('leave_request_messages', function (Blueprint $table) {
            if (Schema::hasColumn('leave_request_messages', 'reply_to_message_id')) {
                $table->dropColumn('reply_to_message_id');
            }
            if (Schema::hasColumn('leave_request_messages', 'deleted_for_everyone_at')) {
                $table->dropColumn('deleted_for_everyone_at');
            }
        });
    }
};
