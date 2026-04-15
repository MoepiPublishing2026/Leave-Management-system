<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leave_policies')->where('leave_type', 'Urgent')->delete();
    }

    public function down(): void
    {
    }
};

