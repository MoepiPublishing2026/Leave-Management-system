<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('manager_departments')) {
            return;
        }

        Schema::create('manager_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('department');
            $table->timestamps();

            $table->unique('department');
            $table->unique(['user_id', 'department']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_departments');
    }
};
