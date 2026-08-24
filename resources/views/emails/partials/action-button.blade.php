{{--
    Cross-client button for Outlook desktop, Outlook web, Gmail, Apple Mail, Yahoo, and mobile.
    Outlook Word gets a VML button; every other client (including webmail) gets the HTML table button.
--}}
@php
    $url = $url ?? '#';
    $label = $label ?? 'Open';
    $bg = $bg ?? '#a2d109';
    $color = $color ?? '#0a1347';
    $width = (int) ($width ?? max(150, (strlen($label) * 9) + 48));
@endphp
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:44px;v-text-anchor:middle;width:{{ $width }}px;" arcsize="50%" stroke="f" fillcolor="{{ $bg }}">
    <w:anchorlock/>
    <center style="color:{{ $color }};font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;font-size:15px;font-weight:bold;">{{ $label }}</center>
</v:roundrect>
<![endif]-->
<!--[if !mso]><!-->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-collapse:separate; line-height:100%;">
    <tr>
        <td align="center" valign="middle" bgcolor="{{ $bg }}" style="background-color:{{ $bg }}; border-radius:25px;">
            <a href="{{ $url }}" target="_blank" rel="noopener"
               style="background-color:{{ $bg }}; border-top:12px solid {{ $bg }}; border-right:22px solid {{ $bg }}; border-bottom:12px solid {{ $bg }}; border-left:22px solid {{ $bg }}; border-radius:25px; color:{{ $color }} !important; display:inline-block; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:15px; font-weight:700; line-height:1.2; text-align:center; text-decoration:none; -webkit-text-size-adjust:none;">
                <span style="color:{{ $color }}; font-weight:700;">{{ $label }}</span>
            </a>
        </td>
    </tr>
</table>
<!--<![endif]-->
