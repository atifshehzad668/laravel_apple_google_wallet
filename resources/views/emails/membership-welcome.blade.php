<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $organizationName }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .member-info {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .member-info p {
            margin: 8px 0;
        }
        .member-info strong {
            color: #667eea;
        }
        .wallet-buttons {
            margin: 30px 0;
        }
        .wallet-button {
            display: inline-block;
            padding: 14px 28px;
            margin: 10px 10px 10px 0;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
        }
        .apple-wallet {
            background: #000;
            color: #fff;
        }
        .google-wallet {
            background: #4285f4;
            color: #fff;
        }
        .footer {
            background: #f5f7fa;
            padding: 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to {{ $organizationName }}!</h1>
        </div>

        <div class="content">
            <h2>Hello {{ $member->first_name }}!</h2>
            
            <p>Thank you for registering with {{ $organizationName }}. We're excited to have you as a member!</p>

            <div class="member-info">
                <p><strong>Your Member ID:</strong> {{ $member->unique_member_id }}</p>
                <p><strong>Name:</strong> {{ $member->full_name }}</p>
                <p><strong>Email:</strong> {{ $member->email }}</p>
                <p><strong>Mobile:</strong> {{ $member->mobile }}</p>
                <p><strong>Member Since:</strong> {{ $member->created_at->format('F j, Y') }}</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <h3 style="margin-bottom: 15px;">Your Membership QR Code</h3>
                <div style="background: white; padding: 20px; display: inline-block; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ $member->unique_member_id }}&choe=UTF-8" alt="Membership QR Code" width="200" height="200">
                    <p style="margin-top: 10px; font-family: monospace; font-weight: bold; color: #333;">{{ $member->unique_member_id }}</p>
                </div>
            </div>

            <p style="margin-top: 30px;">
                Your membership card includes this unique QR code. Simply show this at any of our locations for easy access.
            </p>

            <p style="margin-top: 20px;">
                If you have any questions, please don't hesitate to contact us at 
                <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
            </p>

            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>The {{ $organizationName }} Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ $organizationName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
