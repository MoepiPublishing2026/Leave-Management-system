<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:word" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>{{ $title ?? config('app.name') }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        :root { color-scheme: light; supported-color-schemes: light; }
        html, body { margin: 0 !important; padding: 0 !important; height: 100% !important; width: 100% !important; }
        * { -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        table { border-collapse: collapse !important; }
        a, a:link, a:visited { text-decoration: none; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; }
        img { max-width: 100% !important; height: auto !important; }
        p, td, th, a, span { word-break: break-word; overflow-wrap: anywhere; }
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .email-pad { padding: 12px !important; }
            .btn-cell { display: block !important; width: 100% !important; padding: 0 0 12px 0 !important; }
            .btn-cell table,
            .btn-cell td { width: 100% !important; }
            .btn-cell a { display: block !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#ffffff; width:100%;">
    <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all;">
        {{ $preheader ?? '' }}
    </div>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#ffffff;">
        <tr>
            <td class="email-pad" align="left" style="padding:20px;">
                <!--[if mso]>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" align="left"><tr><td>
                <![endif]-->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width:600px;">
                    <tr>
                        <td style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:16px; line-height:1.5; color:#4b4b4b;">
                            @yield('content')
                            <p style="margin:28px 0 0; font-size:16px; line-height:1.5; color:#4b4b4b;">
                                Regards,<br />
                                Moepi Leave Management System
                            </p>
                        </td>
                    </tr>
                </table>
                <!--[if mso]>
                </td></tr></table>
                <![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
