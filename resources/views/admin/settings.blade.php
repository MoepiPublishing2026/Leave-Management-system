@extends($layout ?? 'layouts.admin')

@section('title', 'Leave Policies Settings')

@section('content')
@include('partials.content-page-styles')

<style>
    .app-page {
        box-sizing: border-box;
        width: 100%;
        max-width: 932px;
        margin: 0 auto;
        padding: 0 10px 24px;
        font-family: "Poppins", "Segoe UI", Arial, sans-serif;
        color: #273449;
    }

    .app-page h1 {
        margin: 0 0 5px 8px;
        color: #273449;
        font-size: 36px;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.7px;
    }

    .app-page .app-page-lead {
        margin: 0 0 27px 10px;
        color: #696969;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
    }

    #leavePoliciesForm {
        width: 100%;
        margin: 0;
    }

    .settings-policy-card {
        box-sizing: border-box;
        display: grid;
        grid-template-columns: 250px 75px 105px 150px minmax(170px, 1fr) 64px;
        align-items: center;
        column-gap: 0;
        width: 100%;
        min-height: 72px;
        margin: 0 0 24px;
        padding: 7px 12px 7px 16px;
        background: #ffffff;
        border: 3px solid #d4e500;
        border-radius: 13px;
    }

    .settings-policy-name {
        display: flex;
        align-items: center;
        min-width: 0;
        gap: 17px;
    }

    .settings-policy-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        background: #d4e500;
        color: #111111;
    }

    .settings-policy-icon svg {
        display: block;
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .settings-policy-icon .icon-solid {
        fill: currentColor;
        stroke: none;
    }

    .settings-policy-header {
        margin: 0;
        color: #273449;
        font-size: 18px;
        font-weight: 400;
        line-height: 1.35;
        white-space: nowrap;
    }

    .settings-policy-field {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-width: 0;
        margin: 0;
        text-align: center;
    }

    .settings-policy-field > label,
    .settings-policy-field > .settings-field-label {
        display: block;
        margin: 0;
        padding: 0;
        color: #273449;
        font-size: 12px;
        font-weight: 400;
        line-height: 1.25;
        white-space: nowrap;
    }

    .settings-policy-value {
        box-sizing: border-box;
        display: block;
        width: 100%;
        max-width: 65px;
        height: 17px;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        outline: none;
        background: transparent;
        box-shadow: none;
        color: #273449;
        font-family: inherit;
        font-size: 14px;
        font-weight: 400;
        line-height: 17px;
        text-align: center;
        appearance: textfield;
        -moz-appearance: textfield;
    }

    .settings-policy-value::-webkit-inner-spin-button,
    .settings-policy-value::-webkit-outer-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }

    .settings-policy-value:focus {
        border-bottom: 1px solid #a8b900;
        outline: none;
        box-shadow: none;
    }

    .settings-status {
        box-sizing: border-box;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-width: 88px;
        min-height: 19px;
        margin: 0;
        padding: 2px 10px;
        border: 0;
        border-radius: 20px;
        font-family: inherit;
        font-size: 11px;
        font-weight: 400;
        line-height: 1.25;
        white-space: nowrap;
    }

    .settings-status--allowed,
    .settings-status--required {
        background: #d4e500;
        color: #26321c;
    }

    .settings-status--disabled,
    .settings-status--optional {
        background: #cbd4dd;
        color: #344256;
    }

    .settings-status svg {
        flex: 0 0 12px;
        width: 12px;
        height: 12px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .settings-policy-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
    }

    .settings-icon-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 19px;
        width: 19px;
        height: 24px;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        color: #222222;
        cursor: pointer;
    }

    .settings-icon-button svg {
        display: block;
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .settings-icon-button:hover {
        background: transparent;
        color: #829000;
    }

    .settings-icon-button:focus-visible {
        outline: 2px solid #a8b900;
        outline-offset: 2px;
        border-radius: 3px;
    }

    .settings-policy-options {
        display: none;
    }

    .settings-policy-card.is-editing .settings-policy-options {
        display: flex;
        grid-column: 1 / -1;
        flex-wrap: wrap;
        gap: 16px 28px;
        padding: 14px 0 4px 47px;
    }

    .settings-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #273449;
        font-size: 13px;
        cursor: pointer;
    }

    .settings-checkbox-label input {
        width: 15px;
        height: 15px;
        margin: 0;
        accent-color: #bdce00;
    }

    .settings-policy-card.is-editing {
        row-gap: 8px;
    }

    .settings-actions {
        display: flex;
        justify-content: flex-end;
        margin: 8px 0 24px;
        margin-top: 200px;
    }

    .settings-actions .app-page-btn {
        min-height: 40px;
        padding: 10px 22px;
        border: 0;
        border-radius: 7px;
        background: #d4e500;
        color: #273449;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .settings-actions .app-page-btn:hover {
        background: #c4d500;
    }

    .settings-footer-row {
        display: flex;
        align-items: flex-start;
        gap: 200px;
        margin-top: 32px;
    }

    .settings-notes-panel {
        box-sizing: border-box;
        flex: 1 1 auto;
        min-width: 0;
        padding: 24px;
        border: 1px solid #e4d5a8;
        border-radius: 12px;
        background: #f6e7ba;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .settings-notes .app-page-section-title {
        margin: 0 0 14px;
        color: #b45309;
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .settings-notes .app-info-box {
        box-sizing: border-box;
        width: 100%;
        padding: 18px 24px;
        border-radius: 9px;
        background: #fff9e9;
        color: #555555;
        font-size: 14px;
    }

    .settings-notes .app-info-box ul {
        margin: 0;
        padding-left: 20px;
    }

    .settings-notes .app-info-box li {
        margin-bottom: 7px;
    }

    @media (max-width: 1000px) and (min-width: 761px) {
        .settings-policy-card {
            grid-template-columns:
                minmax(190px, 1.6fr)
                60px
                75px
                130px
                minmax(145px, 1.2fr)
                54px;
        }

        .settings-policy-header {
            font-size: 16px;
        }

        .settings-policy-name {
            gap: 10px;
        }

        .settings-policy-field > label,
        .settings-policy-field > .settings-field-label {
            font-size: 11px;
        }
    }

    @media (max-width: 760px) {
        .app-page {
            max-width: 100%;
            padding-right: 8px;
            padding-left: 8px;
        }

        .app-page h1 {
            margin-left: 0;
            font-size: 29px;
        }

        .app-page .app-page-lead {
            margin-left: 0;
            margin-bottom: 22px;
        }

        .settings-policy-card {
            grid-template-columns: minmax(0, 1fr) 52px 75px;
            gap: 14px 6px;
            padding: 14px;
            margin-bottom: 16px;
        }

        .settings-policy-name {
            grid-column: 1 / -1;
            gap: 12px;
        }

        .settings-policy-header {
            font-size: 17px;
            white-space: normal;
        }

        .settings-policy-field {
            align-items: flex-start;
            text-align: left;
        }

        .settings-policy-field > label,
        .settings-policy-field > .settings-field-label {
            white-space: normal;
        }

        .settings-policy-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }

        .settings-policy-card.is-editing .settings-policy-options {
            padding-left: 0;
        }

    
        .settings-footer-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 40px;
            margin-top: 36px;
            width: 100%;
        }

        .settings-notes-panel {
            flex: 0 0 66%;
            max-width: 620px;
            padding: 26px 30px;
            background: #e9ddb7;
            border: 1px solid #c9b57e;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .settings-actions {
            flex: 0 0 auto;
            display: flex;
            align-items: right-end;
            justify-content: flex-end;
            margin: 0;
        }

        .settings-actions .app-page-btn {
            min-width: 155px;
            height: 48px;
            padding: 0 24px;
            border: none;
            border-radius: 10px;
            background: #d4e500;
            color: #000;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
    
        }

        .settings-actions .app-page-btn:hover {
            background: #c4d500;
        }
    }
</style>

<div class="app-page">
    <h1>Leave Policies</h1>

    <p class="app-page-lead">
        Configure default days, carry-over limits, and rules for each leave type. Changes apply to new requests immediately.
    </p>

    <form id="leavePoliciesForm"
          action="{{ route($formRoute ?? 'admin.settings.save') }}"
          method="POST"
          novalidate>
        @csrf

        @foreach($leaveTypes as $type)
            @php
                $policy = $policies[$type] ?? null;
                $days = (int) ($policy->default_days ?? 0);
                $carryOver = (int) ($policy->max_carry_over ?? 0);
                $halfDays = !empty($policy) && (bool) $policy->allow_half_days;
                $requiresProof = !empty($policy) && (bool) $policy->requires_proof;
                $leaveName = strtolower($type);
            @endphp

            <input type="hidden" name="leave_type[]" value="{{ $type }}">

            <section class="settings-policy-card"
                     data-leave-type="{{ $type }}"
                     aria-labelledby="policy-{{ $type }}-label">

                <div class="settings-policy-name">
                    <span class="settings-policy-icon" aria-hidden="true">
                        @if(str_contains($leaveName, 'annual'))
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="5" width="16" height="15" rx="2"></rect>
                                <path d="M8 3v4M16 3v4M4 9h16"></path>
                                <path d="M8 12h1M12 12h1M16 12h1M8 15h1M12 15h1M16 15h1"></path>
                            </svg>
                        @elseif(str_contains($leaveName, 'sick'))
                            <svg viewBox="0 0 24 24" class="icon-solid">
                                <path d="M10 3h4v7h7v4h-7v7h-4v-7H3v-4h7z"></path>
                            </svg>
                        @elseif(str_contains($leaveName, 'maternity'))
                            <svg viewBox="0 0 24 24">
                                <path d="M5 7h3l2 3"></path>
                                <path d="M10 10c1-2 4-3 6-1l3 3-2 3-4-2-2 5H7l2-7"></path>
                                <circle cx="15" cy="5" r="2"></circle>
                                <path d="M5 18h5M14 18h5M6 21h4M14 21h4"></path>
                            </svg>
                        @elseif(str_contains($leaveName, 'paternity'))
                            <svg viewBox="0 0 24 24" class="icon-solid">
                                <circle cx="12" cy="6" r="4"></circle>
                                <path d="M6 12h12l2 9H4z"></path>
                                <path d="M9 15l3 2 3-2-1 4h-4z" fill="#d4e500"></path>
                            </svg>
                        @elseif(str_contains($leaveName, 'bereavement'))
                            <svg viewBox="0 0 24 24">
                                <path d="M12 21s-8-4.5-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.5-8 11-8 11z"></path>
                                <path d="M12 5v7M9 8h6M9 15h6"></path>
                            </svg>
                        @elseif(str_contains($leaveName, 'unpaid'))
                            <svg viewBox="0 0 24 24" class="icon-solid">
                                <rect x="6" y="4" width="4" height="16" rx="1"></rect>
                                <rect x="14" y="4" width="4" height="16" rx="1"></rect>
                            </svg>
                        @elseif(str_contains($leaveName, 'family'))
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="7" r="3"></circle>
                                <circle cx="5" cy="10" r="2"></circle>
                                <circle cx="19" cy="10" r="2"></circle>
                                <path d="M6 20v-2a6 6 0 0 1 12 0v2z"></path>
                                <path d="M2 19v-2a3 3 0 0 1 4-2.8M22 19v-2a3 3 0 0 0-4-2.8"></path>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="5" width="16" height="15" rx="2"></rect>
                                <path d="M8 3v4M16 3v4M4 9h16"></path>
                            </svg>
                        @endif
                    </span>

                    <h2 id="policy-{{ $type }}-label" class="settings-policy-header">
                        {{ $type }} Leave
                    </h2>
                </div>

                <div class="settings-policy-field">
                    <label for="default_days_{{ $type }}">Days</label>
                    <input
                        class="settings-policy-value"
                        id="default_days_{{ $type }}"
                        type="number"
                        min="0"
                        name="default_days[{{ $type }}]"
                        value="{{ $days }}">
                </div>

                <div class="settings-policy-field">
                    <label for="max_carry_over_{{ $type }}">Carryover</label>
                    <input
                        class="settings-policy-value"
                        id="max_carry_over_{{ $type }}"
                        type="number"
                        min="0"
                        name="max_carry_over[{{ $type }}]"
                        value="{{ $carryOver }}">
                </div>

                <div class="settings-policy-field">
                    <span class="settings-field-label">Half-day request</span>

                    <span class="settings-status {{ $halfDays ? 'settings-status--allowed' : 'settings-status--disabled' }}">
                        @if($halfDays)
                            <svg viewBox="0 0 16 16" aria-hidden="true">
                                <path d="m3 8 3 3 7-7"></path>
                            </svg>
                            Allowed
                        @else
                            Not allowed
                        @endif
                    </span>

                    <input
                        type="checkbox"
                        id="allow_half_days_{{ $type }}"
                        name="allow_half_days[{{ $type }}]"
                        value="1"
                        {{ $halfDays ? 'checked' : '' }}
                        hidden>
                </div>

                <div class="settings-policy-field">
                    <span class="settings-field-label">Supporting documentation</span>

                    <span class="settings-status {{ $requiresProof ? 'settings-status--required' : 'settings-status--optional' }}">
                        @if($requiresProof)
                            <svg viewBox="0 0 16 16" aria-hidden="true">
                                <path d="m3 8 3 3 7-7"></path>
                            </svg>
                            Required
                        @else
                            Not required
                        @endif
                    </span>

                    <input
                        type="checkbox"
                        id="requires_proof_{{ $type }}"
                        name="requires_proof[{{ $type }}]"
                        value="1"
                        {{ $requiresProof ? 'checked' : '' }}
                        hidden>
                </div>

                <div class="settings-policy-actions">
                    <button
                        type="button"
                        class="settings-icon-button"
                        aria-label="Edit {{ $type }} leave policy"
                        title="Edit policy"
                        onclick="togglePolicyEdit(this)">

                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"></path>
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="settings-icon-button"
                        aria-label="Delete {{ $type }} leave policy"
                        title="Delete policy"
                        onclick="resetPolicy(this)">

                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 6h18"></path>
                            <path d="M8 6V4h8v2"></path>
                            <path d="m5 6 1 15h12l1-15"></path>
                            <path d="M10 10v7M14 10v7"></path>
                        </svg>
                    </button>
                </div>

                <div class="settings-policy-options">
                    <label class="settings-checkbox-label">
                        <input
                            type="checkbox"
                            class="settings-edit-half-days"
                            {{ $halfDays ? 'checked' : '' }}>
                        Allow half-day requests
                    </label>

                    <label class="settings-checkbox-label">
                        <input
                            type="checkbox"
                            class="settings-edit-proof"
                            {{ $requiresProof ? 'checked' : '' }}>
                        Require supporting documentation
                    </label>
                </div>
            </section>
        @endforeach

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
                <button
                    type="submit"
                    class="app-page-btn app-page-btn--primary"
                    aria-label="Save leave policies">
                    Save policies
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function togglePolicyEdit(button) {
        const card = button.closest('.settings-policy-card');
        const options = card.querySelector('.settings-policy-options');

        card.classList.toggle('is-editing');

        if (card.classList.contains('is-editing')) {
            options.querySelector('.settings-edit-half-days').checked =
                card.querySelector('[name^="allow_half_days"]').checked;

            options.querySelector('.settings-edit-proof').checked =
                card.querySelector('[name^="requires_proof"]').checked;
        }
    }

    document.querySelectorAll('.settings-policy-card').forEach(function(card) {
        const halfDaysEditor = card.querySelector('.settings-edit-half-days');
        const proofEditor = card.querySelector('.settings-edit-proof');

        halfDaysEditor.addEventListener('change', function() {
            card.querySelector('[name^="allow_half_days"]').checked = this.checked;
            updatePolicyStatus(card);
        });

        proofEditor.addEventListener('change', function() {
            card.querySelector('[name^="requires_proof"]').checked = this.checked;
            updatePolicyStatus(card);
        });
    });

    function updatePolicyStatus(card) {
        const halfDays = card.querySelector('[name^="allow_half_days"]').checked;
        const requiresProof = card.querySelector('[name^="requires_proof"]').checked;
        const statuses = card.querySelectorAll('.settings-policy-field .settings-status');

        statuses[0].className = 'settings-status ' +
            (halfDays ? 'settings-status--allowed' : 'settings-status--disabled');

        statuses[0].innerHTML = halfDays
            ? '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3 8 3 3 7-7"></path></svg>Allowed'
            : 'Not allowed';

        statuses[1].className = 'settings-status ' +
            (requiresProof ? 'settings-status--required' : 'settings-status--optional');

        statuses[1].innerHTML = requiresProof
            ? '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3 8 3 3 7-7"></path></svg>Required'
            : 'Not required';
    }

    function resetPolicy(button) {
        const card = button.closest('.settings-policy-card');
        const name = card.querySelector('.settings-policy-header').textContent.trim();

        if (!window.confirm('Reset the settings for ' + name + '?')) {
            return;
        }

        card.querySelectorAll('.settings-policy-value').forEach(function(input) {
            input.value = 0;
        });

        card.querySelector('[name^="allow_half_days"]').checked = false;
        card.querySelector('[name^="requires_proof"]').checked = false;

        card.querySelector('.settings-edit-half-days').checked = false;
        card.querySelector('.settings-edit-proof').checked = false;

        updatePolicyStatus(card);
    }
</script>
@endsection
