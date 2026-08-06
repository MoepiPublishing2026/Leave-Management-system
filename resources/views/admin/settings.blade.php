@extends($layout ?? 'layouts.admin')

@section('title', 'Leave Policies Settings')

@section('content')
@include('partials.content-page-styles')
<style>
    .settings-policy-header {
        font-size: 1.15rem;
        font-weight: 800;
        color: #138056;
        margin: 0 0 14px;
    }
    .settings-checkbox-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px 28px;
        margin-bottom: 14px;
    }
    .settings-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        font-size: 0.92rem;
    }
    .settings-checkbox-label input {
        width: 16px;
        height: 16px;
        accent-color: #138056;
    }
    .settings-summary {
        background: #dcfce7;
        border: none;
        border-radius: 10px;
        padding: 14px 16px;
        color: #166534;
        font-size: 0.88rem;
        line-height: 1.55;
    }
    .settings-summary ul {
        margin: 8px 0 0;
        padding-left: 18px;
    }
    .settings-summary li { margin-bottom: 4px; }
    .settings-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 8px;
    }
    .settings-flash {
        margin-bottom: 16px;
        padding: 12px 16px;
        border-radius: 10px;
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #065f46;
        font-weight: 600;
    }
</style>

<div class="app-page">
    <h1>Leave Policies</h1>
    <p class="app-page-lead">Configure default days, carry-over limits, and rules for each leave type. Changes apply to new requests immediately.</p>

    @if (session('saved'))
        <div class="settings-flash" role="status">Leave policies saved successfully.</div>
    @endif

    <form action="{{ route($formRoute ?? 'admin.settings.save') }}" method="POST" novalidate>
        @csrf

        @foreach($leaveTypes as $type)
            <input type="hidden" name="leave_type[]" value="{{ $type }}">
            <section class="app-page-panel" aria-labelledby="policy-{{ $type }}-label">
                <h2 id="policy-{{ $type }}-label" class="settings-policy-header">{{ $type }} Leave</h2>

                <div class="app-form-grid">
                    <div class="app-form-group">
                        <label for="default_days_{{ $type }}">Default days per year</label>
                        <input id="default_days_{{ $type }}" type="number" min="0" name="default_days[{{ $type }}]" value="{{ $policies[$type]->default_days ?? 0 }}">
                    </div>
                    <div class="app-form-group">
                        <label for="max_carry_over_{{ $type }}">Maximum carry over days</label>
                        <input id="max_carry_over_{{ $type }}" type="number" min="0" name="max_carry_over[{{ $type }}]" value="{{ $policies[$type]->max_carry_over ?? 0 }}">
                    </div>
                </div>

                <div class="settings-checkbox-row">
                    <label for="allow_half_days_{{ $type }}" class="settings-checkbox-label">
                        <input id="allow_half_days_{{ $type }}" type="checkbox" name="allow_half_days[{{ $type }}]" value="1" {{ !empty($policies[$type]) && $policies[$type]->allow_half_days ? 'checked' : '' }}>
                        Allow half days
                    </label>
                    <label for="requires_proof_{{ $type }}" class="settings-checkbox-label">
                        <input id="requires_proof_{{ $type }}" type="checkbox" name="requires_proof[{{ $type }}]" value="1" {{ !empty($policies[$type]) && $policies[$type]->requires_proof ? 'checked' : '' }}>
                        Requires proof (medical certificate or documentation)
                    </label>
                </div>

                <div class="settings-summary" aria-live="polite">
                    <strong>Summary</strong>
                    <ul>
                        <li>{{ (int) ($policies[$type]->default_days ?? 0) }} days of {{ strtolower($type) }} leave annually</li>
                        <li>Up to {{ (int) ($policies[$type]->max_carry_over ?? 0) }} unused days can be carried over</li>
                        <li>{{ !empty($policies[$type]->allow_half_days) ? 'Half-day requests are allowed' : 'Half-day requests are not allowed' }}</li>
                        <li>{{ !empty($policies[$type]->requires_proof) ? 'Supporting documentation is required' : 'Supporting documentation is not required' }}</li>
                    </ul>
                </div>
            </section>
        @endforeach

        <div class="settings-actions">
            <button type="submit" class="app-page-btn app-page-btn--primary" aria-label="Save leave policies">Save policies</button>
        </div>
    </form>

    <div class="app-page-panel" style="margin-top:18px;background:#fef9c3;">
        <h2 class="app-page-section-title" style="color:#92400e;">Important notes</h2>
        <div class="app-info-box" style="background:#fefce8;">
            <ul>
                <li>Policy changes apply immediately to new leave requests.</li>
                <li>Existing leave balances are not automatically adjusted.</li>
                <li>To update individual balances, use User Management.</li>
                <li>All changes are logged in the audit trail.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
