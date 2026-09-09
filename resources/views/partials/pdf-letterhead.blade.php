@php
    $emblemFile = public_path('images/letterhead-emblem.png');
    $canEmbedEmblem = extension_loaded('gd') && is_file($emblemFile);
    $emblemSrc = $canEmbedEmblem ? 'images/letterhead-emblem.png' : null;
@endphp
<div class="pdf-accent"></div>
<div class="pdf-header">
    <table class="pdf-header-table" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="pdf-emblem-cell" width="156" valign="middle">
                @if ($emblemSrc)
                    <img class="pdf-emblem" src="{{ $emblemSrc }}" alt="" />
                @endif
            </td>
            <td class="pdf-gap-cell" width="22" valign="middle">
                <img
                    src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                    width="22"
                    height="1"
                    alt=""
                />
            </td>
            <td class="pdf-lockup-cell" valign="middle">
                <table class="pdf-lockup-table" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="pdf-wordmark" valign="middle">MOEPI</td>
                        <td class="pdf-name-gap" width="18" valign="middle">
                            <img
                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                width="18"
                                height="1"
                                alt=""
                            />
                        </td>
                        <td class="pdf-rule-cell" valign="middle">
                            <div class="pdf-rule">&nbsp;</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<div class="pdf-footer">
    <div class="pdf-footer-text">
        <table class="pdf-footer-contacts" align="center" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <span class="pdf-footer-icon">&#9993;</span> info@moepipublishing.co.za
                </td>
                <td>
                    <span class="pdf-footer-icon">&#9678;</span> www.moepipublishing.co.za
                </td>
                <td>
                    <span class="pdf-footer-icon">&#9742;</span> +2787 265 6716
                </td>
            </tr>
        </table>
        <div class="pdf-footer-row">
            <span class="pdf-footer-icon">&#9679;</span>
            MBA Building, 527 Stanza Bopape Street, Arcadia, Pretoria
            <span class="pdf-footer-sep">|</span>
            Office, S0501
        </div>
    </div>
</div>
