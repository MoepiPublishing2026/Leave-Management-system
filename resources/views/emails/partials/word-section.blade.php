@php
    $heading = $heading ?? '';
    $body = $body ?? '—';
@endphp
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse:collapse; margin:0 0 12px;">
    <tr>
        <td style="background-color:#4F81BD; color:#ffffff; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:14px; font-weight:700; padding:10px 12px;">
            {{ $heading }}
        </td>
    </tr>
    <tr>
        <td style="background-color:#ffffff; color:#1e293b; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:14px; line-height:1.5; padding:12px 14px; border:1px solid #cbd5e1;">
            {!! nl2br(e($body)) !!}
        </td>
    </tr>
</table>
