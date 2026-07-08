<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use App\Support\LeaveRequestChat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
                if (!Schema::hasColumn('leave_request_messages', 'voice_path')) {
                    $table->string('voice_path')->nullable();
                }
                if (!Schema::hasColumn('leave_request_messages', 'reply_to_message_id')) {
                    $table->unsignedBigInteger('reply_to_message_id')->nullable();
                }
                if (!Schema::hasColumn('leave_request_messages', 'deleted_for_everyone_at')) {
                    $table->timestamp('deleted_for_everyone_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('leave_request_message_hidden')) {
            Schema::create('leave_request_message_hidden', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->unique(['message_id', 'user_id']);
                $table->foreign('message_id')->references('id')->on('leave_request_messages')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    private function messageVisibleToUserQuery(int $userId): Builder
    {
        return LeaveRequestMessage::query()
            ->whereNotExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                    ->from('leave_request_message_hidden as h')
                    ->whereColumn('h.message_id', 'leave_request_messages.id')
                    ->where('h.user_id', $userId);
            });
    }

    private function userCanAccessMessage(User $user, LeaveRequestMessage $message): bool
    {
        $conversation = $message->conversation;

        return $conversation && $this->userCanAccessConversation($user, $conversation);
    }

    private function conversationsQueryFor(User $user): Builder
    {
        return LeaveRequestChat::conversationsQueryFor($user);
    }

    private function userCanAccessConversation(User $user, LeaveRequestConversation $conversation): bool
    {
        return LeaveRequestChat::userCanAccessConversation($user, $conversation);
    }

    private function managerMayOpenLeaveChat(User $manager, LeaveRequest $leave): bool
    {
        return $leave->managerCanOpenChat($manager);
    }

    private function assignAdminToConversation(LeaveRequestConversation $conversation, User $admin): void
    {
        if (!$conversation->admin_id) {
            $conversation->admin_id = $admin->id;
            $conversation->save();
        }
    }

    private function defaultAdminId(): ?int
    {
        return User::where('role', 'admin')->orderBy('id')->value('id');
    }

    private function isManagerUserId(mixed $userId): bool
    {
        if ($userId === null || $userId === '') {
            return false;
        }

        return User::whereKey((int) $userId)->where('role', 'manager')->exists();
    }

    private function resolveManagerIdForConversation(LeaveRequest $leaveRequest): ?int
    {
        $existingManagerId = LeaveRequestConversation::query()
            ->where('leave_request_id', $leaveRequest->id)
            ->value('manager_id');

        if ($this->isManagerUserId($existingManagerId)) {
            return (int) $existingManagerId;
        }

        foreach ([$leaveRequest->handled_by, $leaveRequest->overdue_escalated_by] as $candidateId) {
            if ($this->isManagerUserId($candidateId)) {
                return (int) $candidateId;
            }
        }

        $managerId = User::where('role', 'manager')->orderBy('id')->value('id');

        return $managerId ? (int) $managerId : null;
    }

    public function index(Request $request)
    {
        $this->ensureChatTablesExist();
        $user = Auth::user();
        if (!in_array($user->role ?? null, ['admin', 'manager'], true)) {
            abort(403);
        }

        $conversationId = $request->query('conversation');

        $conversations = $this->conversationsQueryFor($user)
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
            ->latest('updated_at')
            ->get();

        if ($conversations->isNotEmpty()) {
            $now = Carbon::now();
            LeaveRequestMessage::whereIn('conversation_id', $conversations->pluck('id')->all())
                ->where('sender_id', '!=', $user->id)
                ->whereNull('delivered_at')
                ->update(['delivered_at' => $now]);
        }

        $activeConversation = null;
        $activeMessages = collect();

        if ($conversationId) {
            $activeConversation = $this->conversationsQueryFor($user)
                ->with(['leaveRequest.user', 'manager', 'admin'])
                ->find($conversationId);

            if ($activeConversation) {
                $now = Carbon::now();
                LeaveRequestMessage::where('conversation_id', $activeConversation->id)
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => $now, 'delivered_at' => $now]);

                $activeMessages = $this->messageVisibleToUserQuery($user->id)
                    ->where('conversation_id', $activeConversation->id)
                    ->with(['sender', 'replyTo.sender'])
                    ->oldest()
                    ->get();
            }
        }

        $forwardConversations = $conversations
            ->when($activeConversation, fn ($c) => $c->where('id', '!=', $activeConversation->id))
            ->values();

        return view('messages.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'activeMessages' => $activeMessages,
            'forwardConversations' => $forwardConversations,
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
            if (!$this->managerMayOpenLeaveChat($user, $leaveRequest)) {
                abort(403, 'This conversation belongs to another manager.');
            }

            $conversation = LeaveRequestConversation::firstOrCreate([
                'leave_request_id' => $leaveRequest->id,
                'manager_id' => $user->id,
            ]);

            if (!$conversation->admin_id) {
                $conversation->admin_id = $this->defaultAdminId();
                $conversation->save();
            }
        } else {
            $managerId = $this->resolveManagerIdForConversation($leaveRequest);

            if (!$managerId) {
                return redirect()->route('messages.index')->with('error', 'No manager is available for this request yet.');
            }

            $conversation = LeaveRequestConversation::query()
                ->where('leave_request_id', $leaveRequest->id)
                ->where('manager_id', $managerId)
                ->first();

            if (!$conversation) {
                $conversation = LeaveRequestConversation::create([
                    'leave_request_id' => $leaveRequest->id,
                    'manager_id' => $managerId,
                ]);
            }

            $this->assignAdminToConversation($conversation, $user);
        }

        if (!$conversation || !$this->userCanAccessConversation($user, $conversation)) {
            return redirect()
                ->route('messages.index')
                ->with('error', 'You do not have access to this conversation.');
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

        if (!$this->userCanAccessConversation($user, $conversation)) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'nullable|string|max:2000',
            'voice' => 'nullable|file|mimetypes:audio/webm,audio/ogg,audio/mp4,audio/mpeg,audio/wav,audio/x-wav,video/webm|max:5120',
            'reply_to_message_id' => 'nullable|integer|exists:leave_request_messages,id',
        ]);

        $body = trim((string) ($validated['body'] ?? ''));
        if ($body === '' && !$request->hasFile('voice')) {
            return back()->withErrors(['body' => 'Type a message or record a voice note.'])->withInput();
        }

        $replyToId = $validated['reply_to_message_id'] ?? null;
        if ($replyToId) {
            $replyMessage = LeaveRequestMessage::find($replyToId);
            if (
                !$replyMessage
                || (int) $replyMessage->conversation_id !== (int) $conversation->id
                || !$this->messageVisibleToUserQuery($user->id)->whereKey($replyMessage->id)->exists()
            ) {
                return back()->withErrors(['body' => 'The message you are replying to is not available.'])->withInput();
            }
        }

        if (($user->role ?? null) === 'manager' && !$conversation->admin_id) {
            $conversation->admin_id = $this->defaultAdminId();
            $conversation->save();
        }

        if (($user->role ?? null) === 'admin') {
            $this->assignAdminToConversation($conversation, $user);
        }

        $voicePath = null;
        if ($request->hasFile('voice')) {
            $voicePath = $request->file('voice')->store(
                'message-voices/' . $conversation->id,
                'public'
            );
            if ($body === '') {
                $body = 'Voice message';
            }
        }

        $message = LeaveRequestMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'reply_to_message_id' => $replyToId,
            'body' => $body,
            'voice_path' => $voicePath,
        ]);

        $conversation->touch();

        $senderName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'User';

        $recipientId = ($user->role ?? null) === 'manager'
            ? $conversation->admin_id
            : $conversation->manager_id;

        if (($user->role ?? null) === 'manager') {
            LeaveRequestChat::notifyAdmins($conversation->id, $conversation->leave_request_id, $senderName);
        } elseif ($recipientId) {
            $recipient = User::find($recipientId);
            if ($recipient) {
                $recipient->notify(new MessageReceivedNotification($conversation->id, $conversation->leave_request_id, $senderName));
            }
        }

        return redirect()->route('messages.index', ['conversation' => $conversation->id]);
    }

    public function voice(LeaveRequestMessage $message)
    {
        $this->ensureChatTablesExist();
        $user = Auth::user();
        if (!in_array($user->role ?? null, ['admin', 'manager'], true)) {
            abort(403);
        }

        $conversation = $message->conversation;
        if (!$conversation || !$this->userCanAccessConversation($user, $conversation)) {
            abort(403);
        }

        if (!$message->voice_path || !Storage::disk('public')->exists($message->voice_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($message->voice_path);
    }

    public function destroy(Request $request, LeaveRequestMessage $message)
    {
        $this->ensureChatTablesExist();
        $user = Auth::user();
        if (!in_array($user->role ?? null, ['admin', 'manager'], true)) {
            abort(403);
        }

        if (!$this->userCanAccessMessage($user, $message)) {
            abort(403);
        }

        $validated = $request->validate([
            'scope' => 'required|in:me,everyone',
        ]);

        if ($validated['scope'] === 'everyone') {
            if ((int) $message->sender_id !== (int) $user->id) {
                abort(403, 'Only the sender can delete a message for everyone.');
            }

            $message->deleted_for_everyone_at = Carbon::now();
            $message->save();
        } else {
            DB::table('leave_request_message_hidden')->updateOrInsert(
                [
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                ],
                [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        return redirect()->route('messages.index', ['conversation' => $message->conversation_id]);
    }

    public function forward(Request $request, LeaveRequestMessage $message)
    {
        $this->ensureChatTablesExist();
        $user = Auth::user();
        if (!in_array($user->role ?? null, ['admin', 'manager'], true)) {
            abort(403);
        }

        if (!$this->userCanAccessMessage($user, $message)) {
            abort(403);
        }

        if ($message->isDeletedForEveryone()) {
            return back()->with('error', 'This message can no longer be forwarded.');
        }

        $validated = $request->validate([
            'target_conversation_id' => 'required|integer|exists:leave_request_conversations,id',
        ]);

        $targetConversation = $this->conversationsQueryFor($user)->find($validated['target_conversation_id']);
        if (!$targetConversation) {
            abort(403);
        }

        $forwardBody = $message->isVoice() ? 'Voice message' : trim((string) $message->body);
        if ($forwardBody === '') {
            return back()->with('error', 'There is nothing to forward in this message.');
        }

        $voicePath = null;
        if ($message->voice_path && Storage::disk('public')->exists($message->voice_path)) {
            $extension = pathinfo($message->voice_path, PATHINFO_EXTENSION) ?: 'webm';
            $newPath = 'message-voices/' . $targetConversation->id . '/fwd_' . $message->id . '_' . time() . '.' . $extension;
            Storage::disk('public')->copy($message->voice_path, $newPath);
            $voicePath = $newPath;
        }

        LeaveRequestMessage::create([
            'conversation_id' => $targetConversation->id,
            'sender_id' => $user->id,
            'body' => $forwardBody,
            'voice_path' => $voicePath,
        ]);

        $targetConversation->touch();

        $senderName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'User';

        if (($user->role ?? null) === 'manager') {
            LeaveRequestChat::notifyAdmins($targetConversation->id, $targetConversation->leave_request_id, $senderName);
        } else {
            $recipientId = $targetConversation->manager_id;
            if ($recipientId) {
                $recipient = User::find($recipientId);
                if ($recipient) {
                    $recipient->notify(new MessageReceivedNotification($targetConversation->id, $targetConversation->leave_request_id, $senderName));
                }
            }
        }

        return redirect()->route('messages.index', ['conversation' => $targetConversation->id])
            ->with('success', 'Message forwarded.');
    }
}
