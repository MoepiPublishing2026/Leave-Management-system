{{-- $documentLinks: list of ['label','viewUrl','downloadUrl','extension'] --}}
@if (!empty($documentLinks))
    <p style="margin:0 0 8px;"><strong>Documents</strong></p>
    @foreach ($documentLinks as $file)
        <p style="margin:0 0 8px;">
            {{ $file['label'] }}@if (!empty($file['extension'])) ({{ strtoupper($file['extension']) }})@endif
            —
            <a href="{{ $file['viewUrl'] }}" style="color:#0f766e;font-weight:700;">View</a>
            <span style="color:#94a3b8;"> | </span>
            <a href="{{ $file['downloadUrl'] }}" style="color:#0f766e;font-weight:700;">Download</a>
        </p>
    @endforeach
    @if (!empty($documentNote))
        <p style="margin:0 0 16px;color:#64748b;font-size:13px;">{{ $documentNote }}</p>
    @endif
@endif
