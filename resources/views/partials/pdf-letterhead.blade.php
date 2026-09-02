@php
    $emblemFile = public_path('images/letterhead-emblem.png');
    $canEmbedEmblem = extension_loaded('gd') && is_file($emblemFile);
    $emblemSrc = $canEmbedEmblem ? 'images/letterhead-emblem.png' : null;
    $footerSrc = is_file(public_path('images/pdf-footer.png')) ? 'images/pdf-footer.png' : null;
@endphp
<div class="pdf-accent"></div>
<div class="pdf-header">
    <table class="pdf-header-table" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="pdf-emblem-cell" width="118" valign="middle">
                @if ($emblemSrc)
                    <img class="pdf-emblem" src="{{ $emblemSrc }}" alt="" />
                @endif
            </td>
            <td class="pdf-gap-cell" width="28" valign="middle">
                <img
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                    width="28"
                    height="1"
                    alt=""
                />
            </td>
            <td class="pdf-lockup-cell" valign="middle">
                <table class="pdf-lockup-table" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pdf-wordmark" valign="top">MOEPI</td>
                        <td class="pdf-name-gap" width="20" valign="top">
                            <img
                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                width="20"
                                height="1"
                                alt=""
                            />
                        </td>
                        <td class="pdf-rule-cell" valign="top">
                            <div class="pdf-rule">&nbsp;</div>
                        </td>
                    </tr>
                </table>
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
