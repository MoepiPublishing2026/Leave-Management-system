@props([
    'url',
    'color' => 'primary',
    'align' => 'left',
])
@php
    $themes = [
        'primary' => ['bg' => '#a2d109', 'fg' => '#0a1347'],
        'success' => ['bg' => '#a2d109', 'fg' => '#0a1347'],
        'error' => ['bg' => '#dc2626', 'fg' => '#ffffff'],
    ];
    $theme = $themes[$color] ?? $themes['primary'];
    $bg = $theme['bg'];
    $fg = $theme['fg'];
    $label = trim(strip_tags((string) $slot));
    $width = max(150, (strlen($label) * 9) + 48);
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:24px 0 8px;">
<tr>
<td align="{{ $align }}">
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:44px;v-text-anchor:middle;width:{{ $width }}px;" arcsize="50%" stroke="f" fillcolor="{{ $bg }}">
    <w:anchorlock/>
    <center style="color:{{ $fg }};font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;font-size:15px;font-weight:bold;">{{ $label }}</center>
</v:roundrect>
<![endif]-->
<!--[if !mso]><!-->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate; line-height:100%;">
    <tr>
        <td align="center" valign="middle" bgcolor="{{ $bg }}" style="background-color:{{ $bg }}; border:1px solid {{ $bg }}; border-radius:25px;">
            <a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener"
               style="background-color:{{ $bg }}; border-top:12px solid {{ $bg }}; border-right:22px solid {{ $bg }}; border-bottom:12px solid {{ $bg }}; border-left:22px solid {{ $bg }}; border-radius:25px; color:{{ $fg }} !important; display:inline-block; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:15px; font-weight:700; line-height:1.2; text-align:center; text-decoration:none; -webkit-text-size-adjust:none;">
                <span style="color:{{ $fg }}; font-weight:700;">{{ $slot }}</span>
            </a>
        </td>
    </tr>
</table>
<!--<![endif]-->
</td>
</tr>
</table>
