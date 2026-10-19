@extends($layout ?? 'layouts.admin')

@section('title', 'Leave Policies Settings')

@section('content')
@include('partials.content-page-styles')
<style>
    .settings-policy-header {
        font-size: 1.15rem;
        font-weight: 800;
        color: #369b55;
        margin: 0 0 16px;
    }
    .settings-checkbox-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px 28px;
        margin-bottom: 14px;
    }
 .app-page .app-page-lead {
    font-size: 1.12rem;
    color: #777;
}
.app-page .app-page-panel {
    border: 1px solid #d1d5db;
}
.app-page .app-form-group label {
    font-size: 15px;
    color: #181c21;
}
.app-page .app-form-group input[type="number"]::-webkit-inner-spin-button,
.app-page .app-form-group input[type="number"]::-webkit-outer-spin-button {
    opacity: 1;
    display: block;
}
.app-page .app-form-grid .app-form-group input[type="number"] {
    background-color: #e6f3f2;
}
    .settings-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: #181c21;
        cursor: pointer;
        font-size: 0.92rem;
    }
    .settings-checkbox-label input {
        width: 16px;
        height: 16px;
        accent-color: #138056;
    }
    .settings-summary {
        background: #e6f3f2;
        border: none;
        border-radius: 10px;
        padding: 14px 16px;
        color: #16bc83;
        font-size: 0.88rem;
        line-height: 1.55;
    }
    .settings-summary strong {
    color: #369b55;
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
  .settings-footer-row {
    display: flex;
    align-items: flex-end;
    gap: 120px;
    margin-top: 130px;
}
.settings-notes {
    min-height: 360px;
    padding: 36px 30px;
    background: #efc74f;
}
.settings-notes-panel {
    box-sizing: border-box;
    flex: 0 1 1088px;
    width: 100%;
    min-height: 360px;
    padding: 36px 30px;
    background: #f6e7ba;
    border: 1px solid #d1c7ad;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
    .settings-notes .app-page-section-title {
    margin: 0 0 40px;
    color: #b45309;
    font-size: 2rem;
    font-weight: 800;
    text-transform: uppercase;
}

.settings-notes .app-info-box {
    box-sizing: border-box;
    width: 100%;
    min-height: 0;
    padding: 20px 28px;
    border-radius: 12px;
    background: #fff9e9;
    color: #555;
    font-size: 1.1rem;
}

.settings-notes .app-info-box ul {
    margin: 0;
    /* padding-left: 24px; */
}

.settings-actions {
    display: flex;
    justify-content: flex-end;
    flex: 0 0 222px;
    margin: 0 0 18px;
}

.settings-actions .app-page-btn {
    min-width: 222px;
    min-height: 71px;
    border: 0;
    border-radius: 14px;
    background: #a3b323;
    color: #000;
    font-size: 1.1rem;
    font-weight: 700;
}

@media (max-width: 900px) {
    .settings-footer-row {
        align-items: stretch;
        flex-direction: column;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 40px;
    }
  .settings-notes-panel {
        flex-basis: auto;
        min-height: 0;
    }
    .settings-notes {
        min-height: 0;
        /* max-width:none; */
    }

    .settings-notes .app-page-section-title {
        margin-bottom: 20px;
    }

    .settings-actions {
        justify-content: flex-start;
        flex-basis:auto;
        margin: 0;
    }
    
    /* .app-page .app-form-grid .app-form-group input[type="number"] {
    background-color: #e6f3f2 !important;
} */

    /* .app-form-group input[name^="default_days"],
.app-form-group input[name^="max_carry_over"] {
    background-color: #e6f3f2;
} */
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

       
    </form>

   <div class="settings-footer-row">
    <div class="app-page-panel settings-notes settings-notes-panel">
        <h2 class="app-page-section-title">Important notes</h2>
        <div class="app-info-box">
            <ul>
                <li>Policy changes apply immediately to new leave requests.</li>
                <li>Existing leave balances are not automatically adjusted.</li>
                <li>To update individual balances, use User Management.</li>
                <li>All changes are logged in the audit trail.</li>
            </ul>
        </div>
    </div>

    <div class="settings-actions">
        <button type="submit" class="app-page-btn app-page-btn--primary"
                aria-label="Save leave policies">Save policies</button>
    </div>
</div>
@endsection
