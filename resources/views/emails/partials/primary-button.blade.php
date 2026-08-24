@php
    $url = $url ?? '#';
    $label = $label ?? 'Open in app';
@endphp
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px;">
    <tr>
        <td>
            @include('emails.partials.action-button', [
                'url' => $url,
                'label' => $label,
                'bg' => '#a2d109',
                'color' => '#0a1347',
            ])
        </td>
    </tr>
</table>
