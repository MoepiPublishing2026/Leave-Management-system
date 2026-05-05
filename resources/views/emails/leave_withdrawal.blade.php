<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Leave Withdrawal Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f7f7f7; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 20px; text-align: center; background-color: #1b5e20; color: #ffffff; font-size: 24px; font-weight: bold; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            Leave Management System
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px; color: #333333; font-size: 16px; line-height: 1.5;">
                            <p>Dear Manager,</p>

                            <p>The leave request titled <strong>{{ $leaveRequest->leave_type }}</strong> from employee <strong>{{ $leaveRequest->user->first_name }} {{ $leaveRequest->user->last_name }}</strong> has been <span style="color: #d32f2f; font-weight: bold;">withdrawn</span>.</p>

                            <p>Leave period: <strong>{{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M Y') }}</strong>.</p>

                            <p>Regards,<br/>Leave Management</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 15px; background-color: #eeeeee; font-size: 12px; color: #555555; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            &copy; {{ date('Y') }} Leave Management System. All Rights Reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
