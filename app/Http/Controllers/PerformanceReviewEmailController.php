<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\User;
use App\Support\PerformanceReviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PerformanceReviewEmailController extends Controller
{
    public function review(Request $request, PerformanceReview $review)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This review link is invalid or has expired.');
        }

        $review->load(['employee', 'submitter']);
        $awaiting = $review->isSubmitted();

        return view('emails.performance_review_email', [
            'review' => $review,
            'awaiting' => $awaiting,
            'criteria' => PerformanceReviews::criteria(),
            'scoreLabels' => PerformanceReviews::scoreLabels(),
            'reviewerName' => PerformanceReviews::displayName(PerformanceReviews::finalRater($review->employee))
                ?: $review->reviewer_name,
            'submitUrl' => $awaiting ? PerformanceReviews::signedSubmitUrl($review) : null,
            'flashSuccess' => session('review_success'),
            'flashError' => session('review_error'),
        ]);
    }

    public function submit(Request $request, PerformanceReview $review)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This review link is invalid or has expired.');
        }

        if (! $review->isSubmitted()) {
            return redirect()
                ->to(PerformanceReviews::signedReviewUrl($review))
                ->with('review_success', 'This performance review has already been completed.');
        }

        $scoreRules = ['senior_comment' => ['nullable', 'string', 'max:4000'], 'scores' => ['required', 'array']];
        foreach (array_keys(PerformanceReviews::criteria()) as $key) {
            $scoreRules['scores.'.$key] = ['required', 'integer', 'min:1', 'max:5'];
        }
        $validator = Validator::make($request->all(), $scoreRules);
        if ($validator->fails()) {
            return redirect()
                ->to(PerformanceReviews::signedReviewUrl($review))
                ->withErrors($validator)
                ->withInput();
        }

        $rater = $review->senior
            ?: PerformanceReviews::finalRater($review->employee ?: $review->submitter);
        if (! $rater) {
            return redirect()
                ->to(PerformanceReviews::signedReviewUrl($review))
                ->with('review_error', PerformanceReviews::missingRaterMessage($review->employee ?: $review->submitter));
        }

        PerformanceReviews::complete(
            $review,
            $request->input('scores'),
            $request->input('senior_comment'),
            $rater
        );

        return redirect()
            ->to(PerformanceReviews::signedReviewUrl($review->fresh()))
            ->with('review_success', 'Scores saved. This monthly performance review is marked as reviewed.');
    }
}
