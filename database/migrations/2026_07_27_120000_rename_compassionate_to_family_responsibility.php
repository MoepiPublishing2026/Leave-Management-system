<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('leave_policies')) {
            DB::table('leave_policies')
                ->whereRaw('LOWER(TRIM(leave_type)) = ?', ['compassionate'])
                ->update(['leave_type' => 'Family Responsibility']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('leave_requests')) {
            DB::table('leave_requests')
                ->whereRaw('LOWER(TRIM(leave_type)) = ?', ['compassionate'])
                ->update(['leave_type' => 'Family Responsibility']);
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('leave_policies')) {
            DB::table('leave_policies')
                ->whereRaw('LOWER(TRIM(leave_type)) = ?', ['family responsibility'])
                ->update(['leave_type' => 'Compassionate']);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('leave_requests')) {
            DB::table('leave_requests')
                ->whereRaw('LOWER(TRIM(leave_type)) = ?', ['family responsibility'])
                ->update(['leave_type' => 'Compassionate']);
        }
    }
};
