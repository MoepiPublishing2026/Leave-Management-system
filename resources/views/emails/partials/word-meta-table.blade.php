{{-- $rows: list of row cells, each cell is ['label' => string, 'value' => mixed]. One or two cells per row. --}}
@php
    $rows = $rows ?? [];
    $th = 'background-color:#4F81BD; color:#ffffff; font-family:\'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size:13px; font-weight:700; text-align:left; padding:8px 10px; border:1px solid #4F81BD;';
    $td = 'background-color:#ffffff; color:#1e293b; font-family:\'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; font-size:13px; padding:8px 10px; border:1px solid #cbd5e1;';
@endphp
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse:collapse; margin:0 0 16px;">
    @foreach ($rows as $row)
        @foreach ($row as $cell)
            <tr>
                <th width="38%" style="{{ $th }} width:38%;">{{ $cell['label'] }}</th>
                <td style="{{ $td }}">{{ $cell['value'] }}</td>
            </tr>
        @endforeach
    @endforeach
</table>
