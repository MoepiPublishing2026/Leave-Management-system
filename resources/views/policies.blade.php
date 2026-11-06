@extends('layouts.app')

@section('title', 'Company Leave Policies')

@section('content')
@include('partials.content-page-styles')
<style>
    .policy-card h2 {
        margin: 0 0 10px;
        font-size: 1.2rem;
        line-height: 1.3;
        font-weight: 700;
        color: #273345;
        letter-spacing: 0;
    }
    .policy-card p {
        margin: 0;
        color: #080808;
        line-height: 1.5;
        font-size: 0.95rem;
        font-weight: 400;
    }

    /* ===== Custom Checkbox Styling ===== */
    .settings-checkbox-label {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        color: #334155 !important;
        cursor: pointer !important;
        margin-right: 16px !important;
    }

    /* Hide the default checkbox entirely */
    .settings-checkbox-label input[type="checkbox"] {
        display: none !important;
    }

    /* Draw the custom green checkbox box */
    .custom-checkbox {
        width: 18px !important;
        height: 18px !important;
        border: 2px solid #cbd5e1 !important;
        border-radius: 4px !important;
        background-color: #ffffff !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all 0.15s ease !important;
        flex-shrink: 0 !important;
    }

    /* When the hidden input is checked, turn the box green */
    .settings-checkbox-label input[type="checkbox"]:checked + .custom-checkbox {
        background-color: #16a34a !important; /* The exact green */
        border-color: #16a34a !important;
    }

    /* Draw the white checkmark inside */
    .settings-checkbox-label input[type="checkbox"]:checked + .custom-checkbox::after {
        content: "" !important;
        width: 5px !important;
        height: 10px !important;
        border: solid white !important;
        border-width: 0 2px 2px 0 !important;
        transform: rotate(45deg) !important;
        margin-bottom: 2px !important;
    }

    /* ===== Prototype Match: Leave Policies Grid ===== */
    .app-page .leave-info-grid {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 20px !important;
        margin-top: 24px !important;
    }
    
    .app-page .app-page-panel-1 {
        background: #fff !important;
        border: 3px solid #d4e21a !important;
        border-radius: 8px !important;
        padding: 22px 26px 24px !important;
        box-shadow: none !important;
    }
    
    .app-page .app-page-panel-1 .app-page-section-title {
        margin: 0 0 14px !important;
        font-size: 1.15rem !important;
        font-weight: 700 !important;
        color: #253141 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        text-align: center !important;
    }
    
    .app-page .app-page-panel-1 .app-info-box ul {
        margin: 0 !important;
        padding-left: 22px !important;
        color: #253141 !important;
        font-size: 0.98rem !important;
        line-height: 1.55 !important;
    }
    
    .app-page .app-page-panel-1 .app-info-box p {
        margin: 0 !important;
        color: #475569 !important;
        font-size: 14px !important;
        line-height: 1.6 !important;
    }

    .app-page .app-page-btn--secondary {
        background: #cbe212 !important; 
        color: #0a1347 !important; 
        border: 1px solid #b5cb0e !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        padding: 0 20px !important;
        height: 40px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    @media (max-width: 768px) {
        html body main .app-page {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: hidden !important;
        }
        html body main .app-page > .app-page-header,
        html body main .app-page > .app-page-lead,
        html body main .app-page > .policy-card,
        html body main .app-page > .leave-info-grid {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            box-sizing: border-box !important;
        }
        html body .app-page .app-page-header {
            flex-direction: column !important;
            align-items: stretch !important;
            padding-right: 0 !important;
        }
        html body .app-page .app-page-btn--secondary {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        html body .app-page .leave-info-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
        }
        html body .app-page .app-page-panel,
        html body .app-page .app-page-panel-1 {
            padding: 14px !important;
            box-shadow: none !important;
        }
        html body .app-page .app-badge-row {
            gap: 8px !important;
        }
        html body .app-page .app-badge {
            white-space: normal !important;
        }
    }
</style>

<div class="app-page">
    <div class="app-page-header">
        <h1>Company Leave Policies</h1>
        <a href="{{ route('policies.download') }}" class="app-page-btn app-page-btn--secondary" target="_blank" rel="noopener noreferrer">Download PDF</a>
    </div>

    @foreach ($leaveTypes as $type)
        @php
            $policy = $policies[$type] ?? null;
        @endphp
        <article class="app-page-panel policy-card">
            <h2>{{ $type }} Leave</h2>

            @if ($policy)
                <div class="app-badge-row">
                    <span class="app-badge">{{ (int) $policy->default_days }} days per year</span>
                    @if ($policy->max_carry_over)
                        <span class="app-badge app-badge--muted">Carry over: {{ (int) $policy->max_carry_over }} days</span>
                    @endif
                    @if ($policy->allow_half_days)
                        <span class="app-badge app-badge--muted">Half days allowed</span>
                    @endif
                    @if ($policy->requires_proof)
                        <span class="app-badge">Proof required</span>
                    @endif
                </div>

                <p>@include('partials.policy-leave-type-text', ['type' => $type, 'policy' => $policy])</p>
            @else
                <p>Policy details are currently unavailable.</p>
            @endif
        </article>
    @endforeach
    <div class="leave-info-grid">
        <div class="app-page-panel-1">
            <h2 class="app-page-section-title">Leave application process</h2>
            <div class="app-info-box">
                <ul>
                    <li>Submit leave applications through the system with all required details.</li>
                    <li>Approvals are made by the employee's direct manager.</li>
                    <li>Leave balances are maintained and visible on your dashboard.</li>
                </ul>
            </div>
        </div>

        <div class="app-page-panel-1">
            <h2 class="app-page-section-title">Return to work</h2>
            <div class="app-info-box">
                <ul>
                    <li>Upon return from extended leave, employees must provide certification if applicable and coordinate with HR for any necessary updates.</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection