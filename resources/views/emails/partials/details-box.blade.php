{{-- $rows: list of ['label' => string, 'value' => mixed] --}}
@php
    $rows = $rows ?? [];
@endphp
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:560px; background-color:#f7fcef; border:1px solid #b2d12f; border-radius:8px;">
    <tr>
        <td style="padding:16px 20px;">
            @foreach ($rows as $row)
                <p style="margin:0 0 {{ $loop->last ? '0' : '8px' }}; font-size:15px; line-height:1.45; color:#4a7523; font-weight:600; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"><b>{{ $row['label'] }}:</b> {{ $row['value'] }}</p>
            @endforeach
        </td>
    </tr>
</table>
