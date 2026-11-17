<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\User;
use App\Notifications\PerformanceReviewSubmittedNotification;
use App\Support\ManagerTeam;
use App\Support\PerformanceReviews;
use App\Support\RunsArtisanInBackground;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PerformanceReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = strtolower(trim((string) $request->query('status', 'all')));
        $search = trim((string) $request->query('search', ''));
        $reviewScope = $this->reviewScope($user, $request);
        $query = $this->visibleQuery($user)->with(['employee', 'reviewer', 'senior'])->latest('updated_at');

        if ($reviewScope === 'mine') {
            $query->where('employee_id', $user->id);
        } elseif ($reviewScope === 'team') {
            $query->where('employee_id', '!=', $user->id);
        }

        if (in_array($status, ['draft', 'submitted', 'reviewed'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $searchByPerson = $reviewScope === 'team' || strtolower((string) ($user->role ?? '')) === 'admin';
            $query->where(function ($q) use ($like, $search, $searchByPerson) {
                $q->where('status', 'like', $like)
                    ->orWhere('review_period', 'like', $like)
                    ->orWhere('employee_position', 'like', $like);

                if (ctype_digit($search)) {
                    $q->orWhere('period_year', (int) $search)
                        ->orWhere('period_month', (int) $search)
                        ->orWhere('id', (int) $search);
                }

                if ($searchByPerson) {
                    $q->orWhere('employee_name', 'like', $like)
                        ->orWhere('reviewer_name', 'like', $like);
                }
            });
        }

        return view('reviews.index', [
            'layout' => PerformanceReviews::layout($user),
            'reviews' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'search' => $search,
            'user' => $user,
            'canCreate' => $this->canCreate($user) && $reviewScope !== 'team',
            'reviewScope' => $reviewScope,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($this->canCreate($user), 403);

        $review = new PerformanceReview([
            'review_period' => '1 Month',
            'review_date' => Carbon::today(),
            'period_year' => (int) Carbon::today()->year,
            'period_month' => (int) Carbon::today()->month,
            'employee_id' => $user->id,
            'reviewer_id' => $this->defaultReviewerId($user),
        ]);

        return view('reviews.form', $this->formPayload($user, $review, false));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($this->canCreate($user), 403);

        $validated = $this->validated($request, $user);
        $employee = User::query()->findOrFail($validated['employee_id']);
        abort_unless($this->canReviewEmployee($user, $employee), 403);

        $existing = PerformanceReview::query()
            ->where('employee_id', $employee->id)
            ->where('period_year', $validated['period_year'])
            ->where('period_month', $validated['period_month'])
            ->first();

        if ($existing) {
            if (! $this->canEdit($user, $existing)) {
                $alreadySubmitted = $existing->isSubmitted() || $existing->isReviewed();

                return redirect()
                    ->route(PerformanceReviews::routeName($user, 'show'), $existing)
                    ->with(
                        $alreadySubmitted ? 'success' : 'error',
                        $alreadySubmitted
                            ? 'Monthly performance review submitted.'
                            : 'A review for this person and month already exists.'
                    );
            }
            $this->fillReview($existing, $validated, $user, $employee);
            $existing->save();
            $review = $existing;
        } else {
            $review = new PerformanceReview;
            $this->fillReview($review, $validated, $user, $employee);
            $review->save();
        }

        if ($request->input('intent') === 'submit') {
            return $this->submitReview($review, $user);
        }

        return redirect()
            ->route(PerformanceReviews::routeName($user, 'edit'), $review)
            ->with('success', 'Draft saved. You can keep editing or submit it.');
    }

    public function show(PerformanceReview $review)
    {
        $user = Auth::user();
        abort_unless($this->canView($user, $review), 403);

        return view('reviews.show', [
            'layout' => PerformanceReviews::layout($user),
            'review' => $review->load(['employee', 'reviewer', 'senior']),
            'user' => $user,
            'criteria' => PerformanceReviews::criteria(),
            'scoreLabels' => PerformanceReviews::scoreLabels(),
            'canEdit' => $this->canEdit($user, $review),
            'canAcknowledge' => $this->canAcknowledge($user, $review),
            'reviewerName' => $this->reviewerDisplayName($review),
            'showSelfScores' => filled($review->self_scores),
            'showFinalScores' => $this->canAcknowledge($user, $review) || $review->isReviewed(),
            'indexParams' => $this->indexParams($user, $review),
        ]);
    }

    public function edit(PerformanceReview $review)
    {
        $user = Auth::user();
        abort_unless($this->canEdit($user, $review), 403);

        return view('reviews.form', $this->formPayload($user, $review, true));
    }

    public function update(Request $request, PerformanceReview $review)
    {
        $user = Auth::user();
        abort_unless($this->canEdit($user, $review), 403);

        $validated = $this->validated($request, $user, $review);
        $employee = User::query()->findOrFail($validated['employee_id']);
        abort_unless($this->canReviewEmployee($user, $employee), 403);

        $this->fillReview($review, $validated, $user, $employee);
        $review->save();

        if ($request->input('intent') === 'submit') {
            return $this->submitReview($review, $user);
        }

        return redirect()
            ->route(PerformanceReviews::routeName($user, 'edit'), $review)
            ->with('success', 'Draft updated.');
    }

    public function submit(PerformanceReview $review)
    {
        $user = Auth::user();
        abort_unless($this->canEdit($user, $review), 403);

        return $this->submitReview($review, $user);
    }

    public function acknowledge(Request $request, PerformanceReview $review)
    {
        $user = Auth::user();
        abort_unless($this->canAcknowledge($user, $review), 403);

        $request->validate([
            'senior_comment' => ['nullable', 'string', 'max:4000'],
            'scores' => ['required', 'array'],
        ]);

        $scoreRules = [];
        foreach (array_keys(PerformanceReviews::criteria()) as $key) {
            $scoreRules['scores.'.$key] = ['required', 'integer', 'min:1', 'max:5'];
        }
        $request->validate($scoreRules);

        $review->senior_id = $review->senior_id ?: $user->id;
        PerformanceReviews::complete(
            $review,
            $request->input('scores'),
            $request->input('senior_comment'),
            $user
        );

        return redirect()
            ->route(PerformanceReviews::routeName($user, 'show'), $review)
            ->with('success', 'Performance review marked as reviewed.');
    }

    private function submitReview(PerformanceReview $review, User $user)
    {
        if (! $review->hasRequiredContent()) {
            return redirect()
                ->route(PerformanceReviews::routeName($user, 'edit'), $review)
                ->with('error', 'Fill in every section before submitting this review.');
        }

        $raters = PerformanceReviews::ratersToNotify($user);
        if ($raters->isEmpty()) {
            return redirect()
                ->route(PerformanceReviews::routeName($user, 'edit'), $review)
                ->with('error', PerformanceReviews::missingRaterMessage($user));
        }

        $review->status = 'submitted';
        $review->submitted_by = $user->id;
        $review->submitted_at = now();
        $review->senior_id = $raters->first()->id;
        $review->save();

        $freshReview = $review->fresh(['employee', 'reviewer']) ?? $review;
        $this->notifyRatersOfSubmission($freshReview, $raters);

        return redirect()
            ->route(PerformanceReviews::routeName($user, 'show'), $review)
            ->with('success', 'Monthly performance review submitted.');
    }

    private function notifyRatersOfSubmission(PerformanceReview $review, Collection $raters): void
    {
        foreach ($raters as $rater) {
            try {
                $rater->notifyNow(new PerformanceReviewSubmittedNotification($review), ['database']);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $raterIds = $raters->pluck('id')->filter()->implode(',');
        if ($raterIds === '') {
            return;
        }

        // Gmail SMTP can hang until PHP's 60s limit; send mail in another process.
        RunsArtisanInBackground::call(
            'reviews:send-submitted-mail',
            (string) $review->id,
            $raterIds
        );
    }

    private function validated(Request $request, User $user, ?PerformanceReview $review = null): array
    {
        $employeeRule = Rule::in($this->reviewableEmployeeIds($user));
        $year = (int) Carbon::today()->year;

        $request->merge([
            'employee_id' => $user->id,
            'period_year' => (int) ($request->input('period_year') ?: ($review->period_year ?? $year)),
            'period_month' => (int) ($request->input('period_month') ?: ($review->period_month ?? Carbon::today()->month)),
            'goals_achieved' => trim((string) $request->input('goals_achieved')),
            'quality_of_work' => trim((string) $request->input('quality_of_work')),
            'initiative_examples' => trim((string) $request->input('initiative_examples')),
            'areas_for_improvement' => trim((string) $request->input('areas_for_improvement')),
        ]);

        $submitting = strtolower((string) $request->input('intent')) === 'submit';
        $narrative = $submitting
            ? ['required', 'string', 'max:8000']
            : ['nullable', 'string', 'max:8000'];
        $scoreRule = $submitting
            ? ['required', 'integer', 'min:1', 'max:5']
            : ['nullable', 'integer', 'min:1', 'max:5'];

        $rules = [
            'employee_id' => ['required', 'integer', $employeeRule],
            'review_period' => ['required', 'string', 'max:40'],
            'review_date' => ['required', 'date'],
            'period_year' => ['required', 'integer', 'min:2020', 'max:'.($year + 1)],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'goals_achieved' => $narrative,
            'quality_of_work' => $narrative,
            'initiative_examples' => $narrative,
            'areas_for_improvement' => $narrative,
            'self_scores' => [$submitting ? 'required' : 'nullable', 'array'],
        ];

        foreach (array_keys(PerformanceReviews::criteria()) as $key) {
            $rules['self_scores.'.$key] = $scoreRule;
        }

        $validated = $request->validate($rules, [
            'goals_achieved.required' => 'Fill in goals achieved before submitting.',
            'quality_of_work.required' => 'Fill in quality of work before submitting.',
            'initiative_examples.required' => 'Fill in examples of initiative before submitting.',
            'areas_for_improvement.required' => 'Fill in areas for improvement before submitting.',
            'self_scores.required' => 'Rate yourself on every criterion before submitting.',
            'self_scores.*.required' => 'Rate yourself on every criterion before submitting.',
        ]);

        return $validated;
    }

    private function fillReview(PerformanceReview $review, array $validated, User $submitter, User $employee): void
    {
        $reviewerId = $this->defaultReviewerId($submitter, $employee);
        $reviewer = User::query()->find($reviewerId) ?: $submitter;

        $review->fill([
            'employee_id' => $employee->id,
            'reviewer_id' => $reviewer->id,
            'submitted_by' => $submitter->id,
            'employee_name' => PerformanceReviews::displayName($employee),
            'employee_position' => PerformanceReviews::positionLabel($employee),
            'reviewer_name' => PerformanceReviews::displayName($reviewer),
            'review_period' => $validated['review_period'],
            'review_date' => $validated['review_date'],
            'period_year' => $validated['period_year'],
            'period_month' => $validated['period_month'],
            'goals_achieved' => $validated['goals_achieved'] ?? null,
            'quality_of_work' => $validated['quality_of_work'] ?? null,
            'initiative_examples' => $validated['initiative_examples'] ?? null,
            'areas_for_improvement' => $validated['areas_for_improvement'] ?? null,
            'self_scores' => $validated['self_scores'] ?? $review->self_scores,
        ]);

        if (! $review->exists) {
            $review->status = 'draft';
        }
    }

    private function formPayload(User $user, PerformanceReview $review, bool $editing): array
    {
        return [
            'layout' => PerformanceReviews::layout($user),
            'review' => $review,
            'user' => $user,
            'editing' => $editing,
            'employees' => $this->reviewableEmployees($user),
            'reviewerName' => $this->reviewerDisplayName($review, $user),
            'criteria' => PerformanceReviews::criteria(),
            'scoreLabels' => PerformanceReviews::scoreLabels(),
            'indexParams' => $editing
                ? $this->indexParams($user, $review)
                : $this->indexParams($user, new PerformanceReview(['employee_id' => $user->id])),
        ];
    }

    private function reviewScope(User $user, Request $request): string
    {
        if (strtolower((string) ($user->role ?? '')) !== 'manager') {
            return 'all';
        }

        return strtolower(trim((string) $request->query('scope', 'team'))) === 'mine'
            ? 'mine'
            : 'team';
    }

    /** @return array<string, string> */
    private function indexParams(User $user, ?PerformanceReview $review = null): array
    {
        if (strtolower((string) ($user->role ?? '')) !== 'manager') {
            return [];
        }

        if ($review && (int) $review->employee_id === (int) $user->id) {
            return ['scope' => 'mine'];
        }

        return ['scope' => 'team'];
    }

    private function visibleQuery(User $user)
    {
        $role = strtolower((string) ($user->role ?? 'employee'));
        $query = PerformanceReview::query();

        if ($role === 'admin') {
            $managerIds = User::query()
                ->managers()
                ->where('manager_id', $user->id)
                ->pluck('id');

            $query->whereIn('status', ['submitted', 'reviewed']);

            return $query->where(function ($q) use ($managerIds, $user) {
                $q->where('senior_id', $user->id);
                if ($managerIds->isNotEmpty()) {
                    $q->orWhereIn('employee_id', $managerIds);
                }
            });
        }

        if ($role === 'manager') {
            $teamIds = ManagerTeam::employeeIdsFor($user);

            return $query->where(function ($q) use ($user, $teamIds) {
                $q->where('employee_id', $user->id)
                    ->orWhere(function ($team) use ($user, $teamIds) {
                        $team->whereIn('status', ['submitted', 'reviewed'])
                            ->where(function ($inner) use ($user, $teamIds) {
                                $inner->where('senior_id', $user->id);
                                if ($teamIds->isNotEmpty()) {
                                    $inner->orWhereIn('employee_id', $teamIds);
                                }
                            });
                    });
            });
        }

        return $query->where('employee_id', $user->id);
    }

    private function canCreate(User $user): bool
    {
        return in_array(strtolower((string) $user->role), ['employee', 'manager'], true);
    }

    private function canView(User $user, PerformanceReview $review): bool
    {
        return $this->visibleQuery($user)->whereKey($review->id)->exists();
    }

    private function canEdit(User $user, PerformanceReview $review): bool
    {
        if (! $review->isDraft()) {
            return false;
        }

        $role = strtolower((string) $user->role);

        if (in_array($role, ['employee', 'manager'], true)) {
            return (int) $review->employee_id === (int) $user->id;
        }

        return false;
    }

    private function canAcknowledge(User $user, PerformanceReview $review): bool
    {
        if (! $review->isSubmitted()) {
            return false;
        }

        if ((int) $review->senior_id === (int) $user->id) {
            return true;
        }

        $rater = PerformanceReviews::finalRater($review->employee);

        return $rater && (int) $rater->id === (int) $user->id;
    }

    private function canReviewEmployee(User $user, ?User $employee): bool
    {
        if (! $employee) {
            return false;
        }

        return in_array((int) $employee->id, $this->reviewableEmployeeIds($user), true);
    }

    /** @return list<int> */
    private function reviewableEmployeeIds(User $user): array
    {
        return $this->reviewableEmployees($user)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function reviewableEmployees(User $user)
    {
        return collect([$user]);
    }

    private function reviewerDisplayName(PerformanceReview $review, ?User $fallback = null): string
    {
        $subject = $review->employee ?: $fallback;
        $rater = PerformanceReviews::finalRater($subject);
        $name = PerformanceReviews::displayName($rater);

        return $name !== '' ? $name : (string) ($review->reviewer_name ?? '');
    }

    private function defaultReviewerId(User $submitter, ?User $employee = null): int
    {
        $rater = PerformanceReviews::finalRater($employee ?: $submitter);

        return $rater ? (int) $rater->id : (int) $submitter->id;
    }
}
