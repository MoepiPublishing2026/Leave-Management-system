<?php

namespace App\Console\Commands;

use App\Support\MissingProofUnpaidConverter;
use Illuminate\Console\Command;

class ConvertMissingProofLeaveToUnpaidCommand extends Command
{
    protected $signature = 'leave:convert-missing-proof-to-unpaid
                            {--dry-run : Report eligible requests without changing them or sending notifications}';

    protected $description = 'Convert approved deferred-proof leave to Unpaid after the proof deadline';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $eligible = MissingProofUnpaidConverter::candidates()
                ->get()
                ->filter(fn ($leaveRequest): bool => MissingProofUnpaidConverter::shouldConvert($leaveRequest))
                ->count();

            $this->info("{$eligible} leave request(s) are eligible for conversion to Unpaid.");

            return self::SUCCESS;
        }

        $converted = MissingProofUnpaidConverter::convertDue();

        $this->info("Converted {$converted} leave request(s) to Unpaid.");

        return self::SUCCESS;
    }
}
