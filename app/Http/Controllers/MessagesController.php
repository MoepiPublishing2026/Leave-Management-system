<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;

class MessagesController extends Controller
{
    private function ensureChatTablesExist(): void
    {
        if (!Schema::hasTable('leave_request_conversations')) {
            Schema::create('leave_request_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('leave_request_id');
                $table->unsignedBigInteger('manager_id');
                $table->unsignedBigInteger('admin_id')->nullable();
                $table->timestamps();

                $table->unique(['leave_request_id', 'manager_id']);
                $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');
                $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            });
        } elseif (!Schema::hasColumn('leave_request_conversations', 'admin_id')) {
            Schema::table('leave_request_conversations', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable();
            });
        }

        if (!Schema::hasTable('leave_request_messages')) {
            Schema::create('leave_request_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedBigInteger('sender_id');
                $table->text('body');
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
                $table->foreign('conversation_id')->references('id')->on('leave_request_conversations')->onDelete('cascade');
                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            });
        } else {
            Schema::table('leave_request_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('leave_request_messages', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable();
                }
                if (!Schema::hasColumn('leave_request_messages', 'read_at')) {
                    $table->timestamp('read_at')->nullable();
                }
            });
        }
    }

    public function index(Request $request)
    {
        $this->ensureChatTablesExist();
        $user = Auth::user();
        if (!in_array($user->role ?? null, ['admin', 'manager'], true)) {
            abort(403);
        }

        $conversationId = $request->query('conversation');

        $conversationsQuery = LeaveRequestConversation::query()
            ->with([
                'leaveRequest.user',
                'manager',
                'admin',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                },
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($user) {
                    $q->whereNull('read_at')->where('sender_id', '!=', $user->id);
                },
            ])
            ->latest('updated_at');

        if (($user->role ?? null) === 'manager') {
            $conversationsQuery->where('manager_id', $user->id);
        }

        $conversations = $conversationsQuery->get();

        if ($conversations->isNotEmpty()) {
            $now = Carbon::now();
            LeaveRequestMessage::whereIn('conversation_id', $conversations->pluck('id')->all())
                ->where('sender_id', '!=', $user->id)
                ->whereNull('delivered_at')
                ->update(['delivered_at' => $now]);
        }

        $activeConversation = null;
        if ($conversationId) {
            $activeConversation = LeaveRequestConversation::with(['leaveRequest.user', 'manager', 'admin', 'messages.sender'])
                ->find($conversationId);
        }

        if ($activeConversation) {
            if (($user->role ?? null) === 'manager' && $activeConversation->manager_id !== $user->id) {
                abort(403);
            }

            $now = Carbon::now();
            LeaveRequestMessage::where('conversation_id', $activeConversation->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => $now, 'delivered_at' => $now]);
        }

        return view('messages.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
        ]);
    }

    public function openLeaveRequest(LeaveRequest $leaveRequest)
    {
        $this->ensureChatTablesExist();
        $user = Auth::user();
        if (!in_array($user->role ?? null, ['admin', 'manager'], true)) {
            abort(403);
        }

        $conversation = null;
        if (($user->role ?? null) === 'manager') {
            $conversation = LeaveRequestConversation::firstOrCreate([
                'leave_request_id' => $leaveRequest->id,
                'manager_id' => $user->id,
            ]);

            if (!$conversation->admin_id) {
                $conversation->admin_id = User::where('role', 'admin')->value('id');
                $conversation->save();
            }
        } else {
            $conversation = LeaveRequestConversation::where('leave_request_id', $leaveRequest->id)->latest()->first();
            if (!$conversation) {
                $managerId = $leaveRequest->handled_by ?? $leaveRequest->overdue_escalated_by ?? User::where('role', 'manager')->value('id');
                if ($managerId) {
                    $conversation = LeaveRequestConversation::firstOrCreate([
                        'leave_request_id' => $leaveRequest->id,
                        'manager_id' => $managerId,
                    ]);
                }
            }
            if (!$conversation && $leaveRequest->overdue_escalated_by) {
                $conversation = LeaveRequestConversation::firstOrCreate([
                    'leave_request_id' => $leaveRequest->id,
                    'manager_id' => $leaveRequest->overdue_escalated_by,
                ]);
            }

            if ($conversation && !$conversation->admin_id) {
                $conversation->admin_id = $user->id;
                $conversation->save();
            }
        }

        if (!$conversation) {
            return redirect()->route('messages.index');
        }

        return redirect()->route('messages.index', ['conversation' => $conversation->id]);
    }

    public function send(Request $request, LeaveRequestConversation $conversation)
    {
        $this->ensureChatTablesExist();
        $user = Auth::user();
        if (!in_array($user->role ?? null, ['admin', 'manager'], true)) {
            abort(403);
        }

        if (($user->role ?? null) === 'manager' && $conversation->manager_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        if (($user->role ?? null) === 'manager' && !$conversation->admin_id) {
            $conversation->admin_id = User::where('role', 'admin')->value('id');
            $conversation->save();
        }

        $message = LeaveRequestMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $conversation->touch();

        $recipientId = null;
        if (($user->role ?? null) === 'manager') {
            $recipientId = $conversation->admin_id;
        } else {
            $recipientId = $conversation->manager_id;
        }

        if ($recipientId) {
            $recipient = User::find($recipientId);
            if ($recipient) {
                $senderName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'User';
                $recipient->notify(new MessageReceivedNotification($conversation->id, $conversation->leave_request_id, $senderName));
            }
        }

        return redirect()->route('messages.index', ['conversation' => $conversation->id]);
    }
}
