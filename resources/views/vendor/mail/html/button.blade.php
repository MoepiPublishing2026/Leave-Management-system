@php
    // Force readable button colors in all email clients.
    $brand = '#b2cd16';
    $brandHover = '#9ef755';

    $color = $color ?? 'primary';
    $theme = [
        'primary' => $brand,
        'success' => $brand,
        'error' => '#e53e3e',
    ];

    $bg = $theme[$color] ?? $brand;
@endphp

<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td>
                                    <a href="{{ $url }}"
                                       class="button button-{{ $color }}"
                                       target="_blank"
                                       rel="noopener"
                                       style="
                                           background-color: {{ $bg }};
                                           border-top: 12px solid {{ $bg }};
                                           border-right: 22px solid {{ $bg }};
                                           border-bottom: 12px solid {{ $bg }};
                                           border-left: 22px solid {{ $bg }};
                                           border-radius: 8px;
                                           box-sizing: border-box;
                                           color: #ffffff !important;
                                           display: inline-block;
                                           font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                           font-size: 16px;
                                           font-weight: 700;
                                           line-height: 1.2;
                                           text-align: center;
                                           text-decoration: none;
                                           -webkit-text-size-adjust: none;
                                       "
                                    >{{ $slot }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

