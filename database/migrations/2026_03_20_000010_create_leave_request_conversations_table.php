<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_request_conversations')) {
            return;
        }

        Schema::create('leave_request_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_request_id');
            $table->unsignedBigInteger('manager_id');
            $table->timestamps();

            $table->unique(['leave_request_id', 'manager_id']);
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_conversations');
    }
};
