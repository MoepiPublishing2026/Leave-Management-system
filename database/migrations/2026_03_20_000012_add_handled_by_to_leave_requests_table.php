<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leave_requests', 'handled_by')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('handled_by')->nullable()->after('status');
                $table->foreign('handled_by')->references('id')->on('users')->onDelete('set null');
            });
        }
        if (!Schema::hasColumn('leave_requests', 'handled_at')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->timestamp('handled_at')->nullable()->after('handled_by');
            });
        }
        if (!Schema::hasColumn('leave_requests', 'handled_action')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->string('handled_action', 40)->nullable()->after('handled_at');
            });
        }

        if (Schema::hasTable('leave_request_messages') && Schema::hasTable('leave_request_conversations')) {
            $approvedKeys = ['approved', 'Approved'];
            $rejectedKeys = ['rejected', 'Rejected'];

            $targetIds = DB::table('leave_requests')
                ->whereNull('handled_by')
                ->whereIn('status', array_merge($approvedKeys, $rejectedKeys))
                ->pluck('id')
                ->all();

            foreach (array_chunk($targetIds, 200) as $chunk) {
                $messageRows = DB::table('leave_request_messages as m')
                    ->join('leave_request_conversations as c', 'c.id', '=', 'm.conversation_id')
                    ->whereIn('c.leave_request_id', $chunk)
                    ->where(function ($q) {
                        $q->where('m.body', 'like', 'Decision: Approved%')
                            ->orWhere('m.body', 'like', 'Decision: Rejected%');
                    })
                    ->orderBy('m.id', 'desc')
                    ->get(['c.leave_request_id', 'c.manager_id', 'm.body', 'm.created_at']);

                $picked = [];
                foreach ($messageRows as $row) {
                    if (isset($picked[$row->leave_request_id])) {
                        continue;
                    }
                    $picked[$row->leave_request_id] = $row;
                }

                foreach ($picked as $leaveRequestId => $row) {
                    $action = str_contains($row->body, 'Approved') ? 'approved' : 'rejected';
                    DB::table('leave_requests')
                        ->where('id', $leaveRequestId)
                        ->whereNull('handled_by')
                        ->update([
                            'handled_by' => $row->manager_id,
                            'handled_at' => $row->created_at,
                            'handled_action' => $action,
                        ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'handled_by')) {
                $table->dropColumn(['handled_by']);
            }
            if (Schema::hasColumn('leave_requests', 'handled_at')) {
                $table->dropColumn(['handled_at']);
            }
            if (Schema::hasColumn('leave_requests', 'handled_action')) {
                $table->dropColumn(['handled_action']);
            }
        });
    }
};
