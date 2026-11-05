@php
    $iconKey = strtolower((string) ($icon ?? 'pending'));
@endphp
@if (str_contains($iconKey, 'approved'))
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.8"/></svg>
@elseif (str_contains($iconKey, 'rejected'))
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>
@elseif (str_contains($iconKey, 'overdue'))
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5V13"/><path d="M12 16.5h.01"/></svg>
@elseif (str_contains($iconKey, 'withdrawn'))
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14L4 9l5-5"/><path d="M4 9h10a6 6 0 010 12h-3"/></svg>
@else
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
@endif
