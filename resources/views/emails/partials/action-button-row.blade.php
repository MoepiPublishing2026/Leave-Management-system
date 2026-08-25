{{-- $buttons: list of ['url' => string, 'label' => string, 'bg' => string, 'color' => string] --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="left" width="100%" style="margin-top:24px; max-width:100%;">
    <tr>
        @foreach ($buttons as $index => $button)
            <td class="btn-cell" valign="middle" style="padding:0 {{ $index < count($buttons) - 1 ? '12px' : '0' }} 0 0;">
                @include('emails.partials.action-button', $button)
            </td>
        @endforeach
    </tr>
</table>
