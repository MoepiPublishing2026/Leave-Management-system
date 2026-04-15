<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_xx_xx_create_leave_policies_table.php
public function up()
{
    Schema::create('leave_policies', function ($table) {
        $table->id();
        $table->string('leave_type')->unique();
        $table->integer('default_days')->default(0);
        $table->integer('max_carry_over')->default(0);
        $table->boolean('allow_half_days')->default(false);
        $table->boolean('requires_proof')->default(false);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
