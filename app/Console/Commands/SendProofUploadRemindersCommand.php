<?php

namespace App\Console\Commands;

use App\Support\ProofUploadReminderService;
use Illuminate\Console\Command;

class SendProofUploadRemindersCommand extends Command
{
    protected $signature = 'leave:send-proof-reminders';

    protected $description = 'Remind employees to upload doctor\'s letters when the window opens and before the deadline';

    public function handle(): int
    {
        ProofUploadReminderService::syncAll();

        $this->info('Proof upload reminders processed.');

        return self::SUCCESS;
    }
}
