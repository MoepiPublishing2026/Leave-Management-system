{{-- Always-visible text links for clients that strip buttons. $links: list of ['url' => string, 'label' => string, 'color' => string] --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="padding:4px 0 0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:13px; line-height:1.6; color:#4b4b4b;">
            Or use {{ count($links) === 1 ? 'this link' : 'these links' }}:
            @foreach ($links as $index => $link)
                @if ($index > 0)
                    &nbsp;|&nbsp;
                @endif
                <a href="{{ $link['url'] }}" target="_blank" rel="noopener" style="color:{{ $link['color'] ?? '#0f766e' }}; font-weight:700; text-decoration:underline;">{{ $link['label'] }}</a>
            @endforeach
        </td>
    </tr>
</table>
