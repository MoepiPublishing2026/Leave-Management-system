@php
    $logoFile = public_path('images/logo_trimmed.png');
    $canEmbedLogo = extension_loaded('gd') && is_file($logoFile);
    $logoSrc = $canEmbedLogo ? 'images/logo_trimmed.png' : null;
    $footerSrc = is_file(public_path('images/pdf-footer.png')) ? 'images/pdf-footer.png' : null;
@endphp
<div class="pdf-accent"></div>
<div class="pdf-header">
    <table class="pdf-header-table">
        <tr>
            <td style="width: 1%; white-space: nowrap;">
                @if ($logoSrc)
                    <img class="pdf-logo" src="{{ $logoSrc }}" alt="Moepi" />
                @else
                    <div class="pdf-wordmark">MOEPI</div>
                @endif
            </td>
            <td style="width: 99%;">
                <div class="pdf-rule"></div>
            </td>
        </tr>
    </table>
</div>
<div class="pdf-footer">
    @if ($footerSrc)
        <img class="pdf-footer-shape" src="{{ $footerSrc }}" alt="" />
    @endif
    <div class="pdf-footer-text">
        <div class="pdf-footer-row">
            <span class="pdf-footer-icon">&#9993;</span> info@moepipublishing.co.za
            <span class="pdf-footer-sep">|</span>
            <span class="pdf-footer-icon">&#9678;</span> www.moepipublishing.co.za
            <span class="pdf-footer-sep">|</span>
            <span class="pdf-footer-icon">&#9742;</span> +2787 265 6716
        </div>
        <div class="pdf-footer-row">
            <span class="pdf-footer-icon">&#9679;</span>
            MBA Building, 527 Stanza Bopape Street, Arcadia, Pretoria | Office, S0501
        </div>
    </div>
</div>
