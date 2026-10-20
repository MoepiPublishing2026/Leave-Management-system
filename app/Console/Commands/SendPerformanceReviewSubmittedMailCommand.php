<?php

namespace App\Console\Commands;

use App\Models\PerformanceReview;
use App\Models\User;
use App\Notifications\PerformanceReviewSubmittedNotification;
use Illuminate\Console\Command;

class SendPerformanceReviewSubmittedMailCommand extends Command
{
    protected $signature = 'reviews:send-submitted-mail {review} {raters}';

    protected $description = 'Email raters that a monthly performance review was submitted';

    public function handle(): int
    {
        $review = PerformanceReview::query()
            ->with(['employee', 'reviewer'])
            ->find($this->argument('review'));

        if (! $review) {
            return self::SUCCESS;
        }

        $raterIds = collect(explode(',', (string) $this->argument('raters')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($raterIds->isEmpty()) {
            return self::SUCCESS;
        }

        $notification = new PerformanceReviewSubmittedNotification($review);

        foreach (User::query()->whereKey($raterIds->all())->get() as $rater) {
            try {
                $rater->notifyNow($notification, ['mail']);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return self::SUCCESS;
    }
}
