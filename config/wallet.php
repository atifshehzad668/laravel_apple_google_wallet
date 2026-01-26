<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wallet System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Apple Wallet and Google Wallet integration.
    | This includes certificate paths, API credentials, and design settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Apple Wallet Configuration
    |--------------------------------------------------------------------------
    */
    'apple' => [
        // Apple Developer IDs
        'team_id' => env('APPLE_WALLET_TEAM_ID', 'YOUR_TEAM_ID'),
        'pass_type_id' => env('APPLE_WALLET_PASS_TYPE_ID', 'pass.8CS98N8QKD.pinkroommembership'),
        'organization_name' => env('APPLE_WALLET_ORG_NAME', 'Premium Membership Club'),
        
        // Certificate Paths (relative to storage/app/apple/)
        'certificate_path' => storage_path('app/apple/PinkRoomPass.p12'),
        'certificate_password' => env('APPLE_WALLET_CERT_PASSWORD', ''),
        'wwdr_certificate_path' => storage_path('app/apple/AppleWWDRCAG6.cer'),
        
        // Pass Design Settings
        'design' => [
            'description' => 'Membership Card',
            'logo_text' => 'Premium Member',
            'background_color' => 'rgb(15, 23, 42)', // Dark slate
            'foreground_color' => 'rgb(255, 255, 255)', // White
            'label_color' => 'rgb(148, 163, 184)', // Slate gray
            
            // Barcode Settings
            'barcode_format' => 'PKBarcodeFormatQR',
            'barcode_encoding' => 'iso-8859-1',
        ],
        
        // Pass Assets
        'template_path' => storage_path('app/templates/apple-pass/'),
        'output_path' => storage_path('app/passes/apple/'),
        
        // Web Service (optional - for pass updates)
        'web_service_url' => env('APPLE_WALLET_WEB_SERVICE_URL', ''),
        'authentication_token' => env('APPLE_WALLET_AUTH_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Wallet Configuration
    |--------------------------------------------------------------------------
    */
    'google' => [
        // Google Wallet IDs
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID', '3388000000000000000'),
        'class_id' => env('GOOGLE_WALLET_CLASS_ID', 'membership_card_class'),
        
        // Service Account (JSON key file)
        'service_account_file' => storage_path('app/google/google_key.json'),
        
        // API Endpoints
        'api_base_url' => 'https://walletobjects.googleapis.com/walletobjects/v1',
        'jwt_audience' => 'google',
        
        // Pass Design Settings
        'design' => [
            'issuer_name' => env('GOOGLE_WALLET_ISSUER_NAME', 'Premium Membership Club'),
            'program_name' => 'Premium Members',
            'card_title' => 'Member Card',
            
            // Colors (hex format)
            'hex_background_color' => '#0f172a',
            'hex_foreground_color' => '#ffffff',
            
            // Logo URL (must be HTTPS)
            'logo_url' => env('GOOGLE_WALLET_LOGO_URL', 'https://yourdomain.com/images/logo.png'),
            'logo_description' => 'Premium Membership Club Logo',
        ],
        
        // Barcode Settings
        'barcode_type' => 'QR_CODE',
        'barcode_render_encoding' => 'UTF_8',
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding Configuration
    |--------------------------------------------------------------------------
    */
    'branding' => [
        // Organization Details
        'organization_name' => env('ORG_NAME', 'Premium Membership Club'),
        'organization_short_name' => env('ORG_SHORT_NAME', 'PMC'),
        'tagline' => env('ORG_TAGLINE', 'Your Gateway to Exclusive Benefits'),
        
        // Contact Information
        'support_email' => env('ORG_SUPPORT_EMAIL', 'support@premiumclub.com'),
        'support_phone' => env('ORG_SUPPORT_PHONE', '+1 (555) 123-4567'),
        'website' => env('ORG_WEBSITE', 'https://www.premiumclub.com'),
        
        // Color Scheme (Hex codes)
        'colors' => [
            'primary' => '#6366f1',
            'secondary' => '#8b5cf6',
            'accent' => '#ec4899',
            'success' => '#10b981',
            'background' => '#0f172a',
            'text_light' => '#ffffff',
            'text_dark' => '#1e293b',
        ],
        
        // Member ID Format
        'member_id_prefix' => env('MEMBER_ID_PREFIX', 'PMC'),
        'member_id_year_format' => 'Y',
    ],
];
