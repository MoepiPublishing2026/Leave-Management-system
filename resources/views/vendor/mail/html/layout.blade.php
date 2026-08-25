<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:word" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="x-apple-disable-message-reformatting" />
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
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
<style>
@media only screen and (max-width: 600px) {
.inner-body,
.content,
.wrapper,
.footer {
width: 100% !important;
max-width: 100% !important;
}
img {
max-width: 100% !important;
height: auto !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
.content-cell,
.body {
padding: 0 !important;
}
}
</style>
{!! $head ?? '' !!}
</head>
<body style="margin:0; padding:0; background-color:#ffffff; width:100%;">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#ffffff;">
<tr>
<td align="left" style="padding:20px;">
<!--[if mso]>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" align="left"><tr><td>
<![endif]-->
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important; background-color:#ffffff;">
<table class="inner-body" align="left" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<!-- Body content -->
<tr>
<td class="content-cell" style="font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:16px; line-height:1.5; color:#4b4b4b; padding:0;">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
<!--[if mso]>
</td></tr></table>
<![endif]-->
</td>
</tr>
</table>
</body>
</html>
