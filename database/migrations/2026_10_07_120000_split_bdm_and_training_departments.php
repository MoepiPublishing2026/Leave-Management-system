<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $from = 'BDM & Training';
        $to = 'BDM';

        if (Schema::hasTable('users')) {
            DB::table('users')->where('department', $from)->update(['department' => $to]);
            DB::table('users')->where('designation', $from)->update(['designation' => $to]);
        }

        if (Schema::hasTable('manager_departments')) {
            DB::table('manager_departments')->where('department', $from)->update(['department' => $to]);
        }
    }

    public function down(): void
    {
        $from = 'BDM';
        $to = 'BDM & Training';

        if (Schema::hasTable('users')) {
            DB::table('users')->where('department', $from)->update(['department' => $to]);
            DB::table('users')->where('designation', $from)->update(['designation' => $to]);
        }

        if (Schema::hasTable('manager_departments')) {
            DB::table('manager_departments')->where('department', $from)->update(['department' => $to]);
        }
    }
};
