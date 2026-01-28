<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Pass - {{ $member->full_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --wallet-blue: #2563eb;
            --wallet-dark: #1e293b;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .pass-container {
            width: 100%;
            max-width: 380px;
            background-color: var(--wallet-blue);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .pass-header {
            padding: 24px 24px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo {
            width: 40px;
            height: 40px;
            background-color: white;
            border-radius: 50%;
            padding: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .logo img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }
        .issuer-name {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 600;
        }
        .pass-content {
            padding: 0 24px 24px;
            color: white;
        }
        .member-id-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 4px;
        }
        .member-id {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .member-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 24px;
            word-wrap: break-word;
        }
        .qr-section {
            background-color: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 0 20px;
        }
        .qr-code {
            width: 240px;
            height: 240px;
        }
        .pass-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding: 20px 24px;
            background-color: rgba(0, 0, 0, 0.05);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 2px;
        }
        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: white;
        }
        .footer {
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 42px;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            pointer-events: none;
            z-index: 100;
            white-space: nowrap;
            letter-spacing: 5px;
            border: 8px solid rgba(255, 255, 255, 0.3);
            padding: 10px 40px;
            border-radius: 12px;
        }
        .watermark.pending, .watermark.expired { color: #ef4444; border-color: #ef4444; }
        
        .status-message {
            margin-top: 24px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 5px solid #2563eb;
        }
        .status-message.pending, .status-message.expired { border-left-color: #ef4444; }
        .status-message h3 {
            margin: 0 0 8px;
            font-size: 18px;
            color: #1e293b;
        }
        .status-message p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="pass-container">
        <div class="pass-header">
            <div class="logo">
                <img src="{{ config('wallet.google.design.logo_url') }}" alt="Logo">
            </div>
            <span class="issuer-name">{{ config('wallet.google.design.issuer_name', 'Premium Membership') }}</span>
        </div>

        @if($member->walletPass && $member->walletPass->status !== 'active')
            <div class="watermark {{ $member->walletPass->status }}">
                {{ $member->walletPass->status === 'pending' ? 'PASS PENDING' : 'PASS EXPIRED' }}
            </div>
        @endif

        <div class="pass-content">
            <div class="member-id-label">Member #{{ $member->unique_member_id }}</div>
            <div class="member-name">{{ $member->full_name }}</div>

            <div class="qr-section">
                {!! QrCode::size(240)->margin(0)->generate($member->unique_member_id) !!}
            </div>
        </div>

        <div class="pass-details">
            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value text-sm">{{ $member->email }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Mobile</span>
                <span class="detail-value">{{ $member->mobile }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Joined</span>
                <span class="detail-value">{{ $member->created_at->format('M Y') }}</span>
            </div>
            <div class="detail-item" style="text-align: right">
                <span class="detail-label">Status</span>
                <span class="detail-value">{{ strtoupper($member->walletPass->status ?? 'ACTIVE') }}</span>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('wallet.branding.organization_name') }}
        </div>
    </div>
</body>
</html>
