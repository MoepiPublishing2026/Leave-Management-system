<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UserDeletionService
{
    public function deleteUser(User $employee): void
    {
        $userId = (int) $employee->id;

        if (Schema::hasColumn('leave_requests', 'handled_by')) {
            LeaveRequest::where('handled_by', $userId)->update(['handled_by' => null]);
        }
        if (Schema::hasColumn('leave_requests', 'overdue_escalated_by')) {
            LeaveRequest::where('overdue_escalated_by', $userId)->update(['overdue_escalated_by' => null]);
        }

        if (Schema::hasTable('leave_request_messages')) {
            LeaveRequestMessage::where('sender_id', $userId)->delete();
        }

        if (Schema::hasTable('leave_request_conversations')) {
            $hasManagerCol = Schema::hasColumn('leave_request_conversations', 'manager_id');
            $hasAdminCol = Schema::hasColumn('leave_request_conversations', 'admin_id');

            $roleConversationQuery = LeaveRequestConversation::query();
            if ($hasManagerCol || $hasAdminCol) {
                $roleConversationQuery->where(function ($query) use ($userId, $hasManagerCol, $hasAdminCol) {
                    if ($hasManagerCol) {
                        $query->where('manager_id', $userId);
                    }
                    if ($hasAdminCol) {
                        if ($hasManagerCol) {
                            $query->orWhere('admin_id', $userId);
                        } else {
                            $query->where('admin_id', $userId);
                        }
                    }
                });
            } else {
                $roleConversationQuery->whereRaw('1 = 0');
            }

            $roleConversationIds = $roleConversationQuery->pluck('id')->all();

            if (!empty($roleConversationIds)) {
                LeaveRequestMessage::whereIn('conversation_id', $roleConversationIds)->delete();
                LeaveRequestConversation::whereIn('id', $roleConversationIds)->delete();
            }
        }

        $leaves = LeaveRequest::where('user_id', $userId)->get();
        foreach ($leaves as $leave) {
            $this->deleteLeaveRequest($leave);
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $userId)
                ->delete();
        }

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $userId)->delete();
        }

        if (Schema::hasTable('password_reset_tokens') && $employee->email) {
            DB::table('password_reset_tokens')->where('email', $employee->email)->delete();
        }

        if (!empty($employee->profile_picture)) {
            Storage::disk('public')->delete($employee->profile_picture);
        }

        $employee->delete();
    }

    public function purgeOrphanedLeaveRequests(): void
    {
        $validUserIds = User::query()->pluck('id');
        if ($validUserIds->isEmpty()) {
            return;
        }

        LeaveRequest::query()
            ->whereNotIn('user_id', $validUserIds)
            ->each(function (LeaveRequest $leave) {
                $this->deleteLeaveRequest($leave);
            });
    }

    /** Permanently remove a leave request and its attachments, messages, and conversations. */
    public function deleteLeaveRequestRecord(LeaveRequest $leave): void
    {
        $this->deleteLeaveRequest($leave);
    }

    protected function deleteLeaveRequest(LeaveRequest $leave): void
    {
        $this->deleteLeaveAttachments($leave);

        if (Schema::hasTable('leave_request_conversations')) {
            $conversationIds = LeaveRequestConversation::where('leave_request_id', $leave->id)
                ->pluck('id')
                ->all();

            if (!empty($conversationIds)) {
                LeaveRequestMessage::whereIn('conversation_id', $conversationIds)->delete();
                LeaveRequestConversation::whereIn('id', $conversationIds)->delete();
            }
        }

        $leave->delete();
    }

    protected function deleteLeaveAttachments(LeaveRequest $leave): void
    {
        if (empty($leave->file_uploads)) {
            return;
        }

        $files = json_decode($leave->file_uploads, true);
        if (!is_array($files)) {
            $files = [$leave->file_uploads];
        }

        $disk = Storage::disk('public');
        foreach ($files as $path) {
            if (!is_string($path) || trim($path) === '') {
                continue;
            }
            $path = ltrim($path, '/');
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}
