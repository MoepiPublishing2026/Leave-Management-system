@php
    $scope = $scope ?? 'team';
    $personalUrl = $personalUrl ?? null;
    $teamUrl = $teamUrl ?? null;
    $personalLabel = $personalLabel ?? 'My leave';
    $teamLabel = $teamLabel ?? 'Team view';
@endphp

@once
<style>
    .mgr-page-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
        max-width: 1200px;
        margin: 0 auto 18px;
        padding: 0 4px;
    }
    .mgr-page-header__main h1 {
        margin: 0;
        font-size: 34px;
        font-weight: 700;
        color: #0f172a;
        padding: 0;
        border: none;
        letter-spacing: 0.02em;
        line-height: 1.15;
    }
    .mgr-page-header__main p,
    .mgr-page-header__main .requests-page-lead {
        margin: 8px 0 0;
        padding: 0;
        color: #64748b;
        font-size: 0.95rem;
    }
    .mgr-scope-switch {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 16px;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 8px;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        border: 1px solid #680523;
        background: #850621;
        color: #fff;
        transition: background-color 0.15s ease;
    }
    .mgr-scope-switch:hover {
        background: #680523;
        color: #fff;
    }
    .mgr-quick-links {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 12px 40px;
        margin: 4px 0 26px;
        padding: 10px 8px 14px;
    }
    .mgr-quick-link {
        display: inline-block;
        padding: 0 2px 5px;
        background: none;
        border: none;
        border-radius: 0;
        color: #850621;
        font-weight: 700;
        font-size: 1.02rem;
        letter-spacing: 0.02em;
        text-decoration: none;
        border-bottom: 3px solid #850621;
        box-shadow: none;
        white-space: nowrap;
        cursor: pointer;
        transition: color 0.15s ease, border-color 0.15s ease;
    }
    .mgr-quick-link:hover {
        background: none;
        color: #680523;
        border-bottom-color: #680523;
    }
    @media (max-width: 520px) {
        .mgr-page-header__main h1 {
            font-size: 28px;
        }
        .mgr-scope-switch {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endonce

@if($scope === 'team' && $personalUrl)
    <a href="{{ $personalUrl }}" class="mgr-scope-switch">{{ $personalLabel }}</a>
@elseif($scope === 'personal' && $teamUrl)
    <a href="{{ $teamUrl }}" class="mgr-scope-switch">{{ $teamLabel }}</a>
@endif
