<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\User;
use App\Notifications\PerformanceReviewSubmittedNotification;
use App\Support\LeaveApprovalWorkflow;
use App\Support\PerformanceReviews;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PerformanceReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = strtolower(trim((string) $request->query('status', 'all')));
        $search = trim((string) $request->query('search', ''));
        $query = $this->visibleQuery($user)->with(['employee', 'reviewer', 'senior'])->latest('updated_at');

        if (in_array($status, ['draft', 'submitted', 'reviewed'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('employee_name', 'like', $like)
                    ->orWhere('reviewer_name', 'like', $like)
                    ->orWhere('employee_position', 'like', $like)
                    ->orWhere('status', 'like', $like);
            });
        }

        return view('reviews.index', [
            'layout' => PerformanceReviews::layout($user),
            'reviews' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'search' => $search,
            'user' => $user,
            'canCreate' => $this->canCreate($user),
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
                return redirect()
                    ->route(PerformanceReviews::routeName($user, 'show'), $existing)
                    ->with('error', 'A review for this person and month already exists.');
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

        $seniors = PerformanceReviews::seniorsToNotify($user);
        if ($seniors->isEmpty()) {
            return redirect()
                ->route(PerformanceReviews::routeName($user, 'edit'), $review)
                ->with('error', 'No senior manager is assigned to receive this review.');
        }

        $review->status = 'submitted';
        $review->submitted_by = $user->id;
        $review->submitted_at = now();
        $review->senior_id = $seniors->first()->id;
        $review->save();

        $freshReview = $review->fresh(['employee', 'reviewer']);
        foreach ($seniors as $senior) {
            try {
                $senior->notifyNow(new PerformanceReviewSubmittedNotification($freshReview), ['database']);
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                $senior->notifyNow(new PerformanceReviewSubmittedNotification($freshReview), ['mail']);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route(PerformanceReviews::routeName($user, 'show'), $review)
            ->with('success', 'Monthly performance review submitted.');
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

        $narrative = strtolower((string) $request->input('intent')) === 'submit'
            ? ['required', 'string', 'max:8000']
            : ['nullable', 'string', 'max:8000'];

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
        ];

        $validated = $request->validate($rules, [
            'goals_achieved.required' => 'Fill in goals achieved before submitting.',
            'quality_of_work.required' => 'Fill in quality of work before submitting.',
            'initiative_examples.required' => 'Fill in examples of initiative before submitting.',
            'areas_for_improvement.required' => 'Fill in areas for improvement before submitting.',
        ]);
        unset($validated['scores']);

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
        ];
    }

    private function visibleQuery(User $user)
    {
        $role = strtolower((string) ($user->role ?? 'employee'));
        $query = PerformanceReview::query();

        if ($role === 'admin') {
            $ids = LeaveApprovalWorkflow::reportingLineUserIds($user);
            $query->whereIn('status', ['submitted', 'reviewed']);
            if ($ids->isEmpty()) {
                return $query->where(function ($q) use ($user) {
                    $q->where('senior_id', $user->id)->orWhereRaw('1 = 0');
                });
            }

            return $query->where(function ($q) use ($ids, $user) {
                $q->whereIn('employee_id', $ids)
                    ->orWhereIn('reviewer_id', $ids)
                    ->orWhere('senior_id', $user->id);
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
        return strtolower((string) $user->role) === 'admin'
            && $review->isSubmitted();
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
        $senior = PerformanceReviews::assignedSenior($subject);
        $name = PerformanceReviews::displayName($senior);

        return $name !== '' ? $name : (string) ($review->reviewer_name ?? '');
    }

    private function defaultReviewerId(User $submitter, ?User $employee = null): int
    {
        $senior = PerformanceReviews::assignedSenior($employee ?: $submitter);

        return $senior ? (int) $senior->id : (int) $submitter->id;
    }
}
