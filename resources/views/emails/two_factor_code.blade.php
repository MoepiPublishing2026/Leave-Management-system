@extends('emails.layouts.client-safe', [
    'title' => 'Your two-factor authentication code',
    'preheader' => 'Your login code expires in 10 minutes.',
])

@section('content')
    <p style="margin:0 0 16px;">{{ \App\Support\EmailGreeting::hello($recipientFirstName ?? null) }}</p>
    <p style="margin:0 0 16px;">Your two-factor authentication code is:</p>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="max-width:560px; background-color:#f7fcef; border:1px solid #b2d12f;">
        <tr>
            <td style="padding:18px 20px; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size:32px; font-weight:700; letter-spacing:5px; color:#5c7212; line-height:1.2;">
                {{ $code }}
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0;">This code will expire in 10 minutes. If you did not request this, please ignore this email.</p>
@endsection
