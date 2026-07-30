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

        $converted = 0;
        $failed = 0;

        MissingProofUnpaidConverter::candidates()
            ->with('user')
            ->orderBy('id')
            ->chunkById(100, function ($requests) use (&$converted, &$failed): void {
                foreach ($requests as $leaveRequest) {
                    try {
                        if (MissingProofUnpaidConverter::convert($leaveRequest)) {
                            $converted++;
                        }
                    } catch (\Throwable $e) {
                        $failed++;
                        report($e);
                    }
                }
            });

        $this->info("Converted {$converted} leave request(s) to Unpaid.");
        if ($failed > 0) {
            $this->error("Failed to process {$failed} leave request(s).");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
