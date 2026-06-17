<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'reply_to_message_id',
        'body',
        'voice_path',
        'deleted_for_everyone_at',
    ];

    protected $casts = [
        'deleted_for_everyone_at' => 'datetime',
    ];

    public function isVoice(): bool
    {
        return !empty($this->voice_path);
    }

    public function isDeletedForEveryone(): bool
    {
        return $this->deleted_for_everyone_at !== null;
    }

    public function conversation()
    {
        return $this->belongsTo(LeaveRequestConversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(self::class, 'reply_to_message_id');
    }

    public function displayBody(): string
    {
        if ($this->isDeletedForEveryone()) {
            return 'This message was deleted';
        }

        return (string) $this->body;
    }

    public function copyableText(): string
    {
        if ($this->isDeletedForEveryone()) {
            return '';
        }

        if ($this->isVoice()) {
            return 'Voice message';
        }

        return (string) $this->body;
    }
}
