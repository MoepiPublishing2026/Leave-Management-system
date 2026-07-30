<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('original_leave_type')->nullable()->after('leave_type');
            $table->timestamp('converted_to_unpaid_at')->nullable()->after('original_leave_type');
            $table->text('converted_to_unpaid_reason')->nullable()->after('converted_to_unpaid_at');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn([
                'original_leave_type',
                'converted_to_unpaid_at',
                'converted_to_unpaid_reason',
            ]);
        });
    }
};
