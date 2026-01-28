<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Updated Membership Card - {{ $organizationName }}</title>
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
            <h1>🔄 Your Membership Card Has Been Updated</h1>
        </div>

        <div class="content">
            <h2>Hello {{ $member->first_name }}!</h2>
            
            <p>Your digital membership card has been regenerated and is ready to use.</p>

            <p><strong>Member ID:</strong> {{ $member->unique_member_id }}</p>

            <div style="text-align: center; margin: 30px 0;">
                <h3 style="margin-bottom: 15px;">Your Updated Digital Membership Pass</h3>
                <div style="background: white; padding: 20px; display: inline-block; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    @php
                        $publicPassUrl = route('pass.public_view', ['unique_member_id' => $member->unique_member_id]);
                    @endphp
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($publicPassUrl) }}" alt="Digital Pass QR Code" width="250" height="250">
                    <p style="margin-top: 15px; font-weight: bold; color: #2563eb;">Scan to View Digital Card</p>
                    <p style="margin-top: 5px; font-size: 12px; color: #666;">Or visit: <a href="{{ $publicPassUrl }}" style="color: #2563eb;">{{ $publicPassUrl }}</a></p>
                </div>
            </div>

            <p style="margin-top: 30px;">
                Your updated membership card is ready. Simply show this QR code at any of our locations for easy access.
            </p>

            <p style="margin-top: 30px;">
                If you have any questions, please contact us at 
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
