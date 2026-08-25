<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('senior_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_name');
            $table->string('employee_position')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->string('review_period')->default('1 Month');
            $table->date('review_date')->nullable();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->text('goals_achieved')->nullable();
            $table->text('quality_of_work')->nullable();
            $table->text('initiative_examples')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->json('scores')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('senior_comment')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period_year', 'period_month'], 'performance_reviews_employee_period_unique');
            $table->index(['status', 'senior_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
