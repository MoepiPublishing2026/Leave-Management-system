@extends('layouts.admin')

@section('title','Leave Policies Settings')

@section('content')
<style>
    body, html {
        margin: 0;
        padding: 0;
        background: white;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    h1 {
        font-weight: 700;
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        color: #1f2937;
        border-left: 6px solid #138056ff;
        padding-left: 10px;
    }
    form {
        max-width: 1200px;
        margin: 0 auto 4rem auto;
        background: white;
        border-radius: 1rem;
          box-shadow: 0 0 10px #a7bd8dff;
        padding: 2rem 3rem;
    }
    .policy-section {
        padding: 1.6rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .policy-section:last-child {
        border: none;
    }
    .policy-header {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1rem;
    }
    .inputs-row {
        display: flex;
        gap: 2rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .input-group {
        flex: 1;
        min-width: 280px;
    }
    label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }
    input[type="number"] {
        width: 100%;
        padding: 0.6rem 0.8rem;
        font-size: 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-sizing: border-box;
        outline-offset: 2px;
        transition: outline-color 0.3s ease;
    }
    input[type="number"]:focus {
        outline: 2px solid #3b82f6;
    }
    .checkbox-row {
        display: flex;
        gap: 3rem;
        margin-bottom: 1.5rem;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        user-select: none;
    }
    input[type="checkbox"] {
        width: 1.1rem;
        height: 1.1rem;
        cursor: pointer;
    }
    .policy-summary {
        background: #dff8e0ff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        color: #08330aff;
        font-size: 0.935rem;
        line-height: 1.5;
    }
    .policy-summary ul {
        padding-left: 1.15em;
        margin: 0;
    }
    .policy-summary ul li {
        margin-bottom: 0.5rem;
    }
    button[type="submit"] {
        background: #88b117ff;
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 0.6rem;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        float: right;
        transition: background-color 0.3s ease;
    }
    button[type="submit"]:hover {
        background: #090d16ff;
    }
    .notification {
        max-width: 900px;
        margin: 1rem auto 2rem auto;
        text-align: center;
        font-weight: 600;
        color: #16a34a;
    }
    .important-notes {
        background: #bbf7a3ff;
        color: #0b3816ff;
        padding: 1.5rem 2rem;
        border-radius: 1rem;
        max-width: 1200px;
        margin: 2rem auto;
        font-weight: 600;
        box-shadow: 0 0 5px rgba(66, 133, 244, 0.35);
    }
    .important-notes ul {
        padding-left: 1em;
        margin-top: 0.5rem;
    }
    .important-notes ul li {
        margin-bottom: 0.4em;
        font-weight: 600;
    }

</style>

<h1>Leave Policies Settings</h1>

<form action="{{ route('admin.settings.save') }}" method="POST" novalidate>
    @csrf
    @foreach($leaveTypes as $type)
        <input type="hidden" name="leave_type[]" value="{{ $type }}">
        <section class="policy-section" aria-labelledby="policy-{{ $type }}-label">
            <h2 id="policy-{{ $type }}-label" class="policy-header">{{ $type }} Leave</h2>
            <div class="inputs-row">
                <div class="input-group">
                    <label for="default_days_{{ $type }}">Default Days per Year</label>
                    <input id="default_days_{{ $type }}" type="number" min="0" name="default_days[{{ $type }}]" value="{{ $policies[$type]->default_days ?? 0 }}">
                </div>
                <div class="input-group">
                    <label for="max_carry_over_{{ $type }}">Maximum Carry Over Days</label>
                    <input id="max_carry_over_{{ $type }}" type="number" min="0" name="max_carry_over[{{ $type }}]" value="{{ $policies[$type]->max_carry_over ?? 0 }}">
                </div>
            </div>
            <div class="checkbox-row">
                <label for="allow_half_days_{{ $type }}" class="checkbox-label">
                    <input id="allow_half_days_{{ $type }}" type="checkbox" name="allow_half_days[{{ $type }}]" value="1" {{ !empty($policies[$type]) && $policies[$type]->allow_half_days ? 'checked' : '' }}>
                    Allow Half Days (Employees can request 0.5 day increments)
                </label>
                <label for="requires_proof_{{ $type }}" class="checkbox-label">
                    <input id="requires_proof_{{ $type }}" type="checkbox" name="requires_proof[{{ $type }}]" value="1" {{ !empty($policies[$type]) && $policies[$type]->requires_proof ? 'checked' : '' }}>
                    Requires Proof (Medical certificate or documentation required)
                </label>
            </div>
            <div class="policy-summary" aria-live="polite">
                <p><strong>Policy Summary</strong></p>
                <ul>
                    <li>Employees receive {{ $policies[$type]->default_days ?? 0 }} days of {{ strtolower($type) }} leave annually</li>
                    <li>Up to {{ $policies[$type]->max_carry_over ?? 0 }} unused days can be carried over to the next year</li>
                    <li>{{ !empty($policies[$type]->allow_half_days) ? 'Half-day requests are allowed' : 'Half-day requests are NOT allowed' }}</li>
                    <li>{{ !empty($policies[$type]->requires_proof) ? 'Supporting documentation is required' : 'Supporting documentation is not required' }}</li>
                </ul>
            </div>
        </section>
    @endforeach
    <button type="submit" aria-label="Save Leave Policies">Save</button>
</form>

<section class="important-notes" aria-label="Important notes about leave policies">
    <p><strong>Important Notes</strong></p>
    <ul>
        <li>Policy changes apply immediately to new leave requests</li>
        <li>Existing leave balances are not automatically adjusted</li>
        <li>To update balances, use the User Management section</li>
        <li>All changes logged in the audit trail</li>
    </ul>
</section>

@endsection
