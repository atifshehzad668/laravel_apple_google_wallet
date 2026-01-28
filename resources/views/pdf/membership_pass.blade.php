<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Membership Pass - {{ $member->full_name }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px 0;
            background-color: #f8fafc;
        }
        .pass-container {
            width: 400px;
            margin: 0 auto;
            background-color: #2563eb;
            border-radius: 20px;
            padding: 25px;
            color: #ffffff;
            position: relative;
        }
        .header {
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
        .card-title {
            font-size: 12px;
            color: #dbeafe;
            text-transform: uppercase;
            margin-top: 5px;
            letter-spacing: 1px;
        }
        .main-info {
            margin-bottom: 20px;
        }
        .member-name {
            font-size: 28px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 2px;
        }
        .member-id {
            font-family: monospace;
            font-size: 14px;
            color: #dbeafe;
        }
        .contact-info {
            margin-top: 15px;
            font-size: 13px;
            color: #ffffff;
        }
        .contact-info table {
            width: 100%;
        }
        .contact-info td {
            padding-bottom: 5px;
        }
        .qr-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .qr-code {
            display: block;
            margin: 0 auto;
            width: 180px;
            height: 180px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #ffffff;
            width: 100%;
        }
        .footer table {
            width: 100%;
        }
        .label {
            display: block;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #dbeafe;
        }
        .value {
            font-size: 13px;
            font-weight: bold;
            color: #ffffff;
        }
        .page-break {
            page-break-after: always;
        }
        .watermark {
            position: absolute;
            top: 250px;
            left: 0;
            transform: rotate(-30deg);
            font-size: 50px;
            font-weight: bold;
            color: rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            z-index: 1000;
            width: 400px;
            text-align: center;
            border: 10px solid rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 20px;
        }
        .watermark.pending, .watermark.expired { 
            color: #ff0000; 
            border-color: #ff0000;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    @php
        $currentStatus = $status ?? ($member->walletPass ? $member->walletPass->status : 'active');
        // Handle temporary 'revoked' status during regeneration
        if ($currentStatus === 'revoked') $currentStatus = 'active';
    @endphp

    <!-- Membership Identification Pass -->
    <div class="pass-container">
        @if($currentStatus !== 'active')
            <div class="watermark {{ $currentStatus }}">
                {{ $currentStatus === 'pending' ? 'PENDING' : 'EXPIRED' }}
            </div>
        @endif
        <div style="margin-bottom: 20px;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="40">
                        <div style="background: white; border-radius: 50%; padding: 5px; width: 30px; height: 30px; text-align: center;">
                            <img src="{{ config('wallet.google.design.logo_url') }}" width="20" height="20" style="vertical-align: middle;">
                        </div>
                    </td>
                    <td style="padding-left: 10px;">
                        <span style="color: white; font-weight: bold; font-size: 14px;">{{ config('wallet.google.design.issuer_name') }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 25px;">
            <div style="color: rgba(255,255,255,0.8); font-size: 12px; margin-bottom: 5px;">Member #{{ $member->unique_member_id }}</div>
            <div style="color: white; font-size: 32px; font-weight: bold; line-height: 1;">{{ $member->full_name }}</div>
        </div>

        <div style="background: white; border-radius: 15px; padding: 20px; text-align: center; margin-bottom: 20px;">
            <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::size(220)->margin(0)->generate($member->unique_member_id)) }}" width="220" height="220">
            <div style="color: #2563eb; font-weight: bold; margin-top: 10px; font-size: 12px;">MEMBER IDENTIFICATION QR</div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 15px;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50%">
                        <div style="color: rgba(255,255,255,0.7); font-size: 10px; text-transform: uppercase;">Email</div>
                        <div style="color: white; font-size: 12px; font-weight: bold;">{{ $member->email }}</div>
                    </td>
                    <td width="50%" align="right">
                        <div style="color: rgba(255,255,255,0.7); font-size: 10px; text-transform: uppercase;">Mobile</div>
                        <div style="color: white; font-size: 12px; font-weight: bold;">{{ $member->mobile }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if($currentStatus !== 'active')
        <div style="margin-top: 20px; padding: 15px; background: #fff1f2; border: 1px solid #fecaca; border-radius: 10px; width: 400px; margin: 20px auto 0;">
            <p style="margin: 0; font-weight: bold; color: #b91c1c; font-size: 14px;">
                {{ $currentStatus === 'pending' ? '[PASS PENDING]' : '[PASS EXPIRED]' }}
            </p>
            <p style="margin: 5px 0 0; color: #7f1d1d; font-size: 12px; line-height: 1.4;">
                {{ $currentStatus === 'pending' 
                    ? 'Your membership pass is currently being processed. Please contact our administration team to complete your activation.' 
                    : 'Your membership has expired. Please visit our office or contact support to renew your membership and reactivate your pass.' 
                }}
            </p>
        </div>
    @endif
</body>
</html>
