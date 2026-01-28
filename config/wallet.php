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
        
        // Certificate Paths
        'certificate_path' => storage_path('app/certificates/PinkRoomPass.p12'),
        'certificate_password' => env('APPLE_WALLET_CERT_PASSWORD', ''),
        'wwdr_certificate_path' => storage_path('app/certificates/AppleWWDRCAG6.cer'),
        
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
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID', '3388000000023068687'),
        'class_id' => env('GOOGLE_WALLET_CLASS_ID', 'appleaccounts1234'),
        
        // Service Account (JSON key file)
        'service_account_file' => storage_path('app/google/apple-accounts.json'),
        
        // API Endpoints
        'api_base_url' => 'https://walletobjects.googleapis.com/walletobjects/v1',
        'jwt_audience' => 'google',
        
        // Pass Design Settings
        'design' => [
            'issuer_name' => env('GOOGLE_WALLET_ISSUER_NAME', 'Apple Account'),
            'program_name' => 'Apple Account Members',
            'card_title' => env('GOOGLE_WALLET_CARD_TITLE', 'Apple Account Pass'),
            
            // Colors (Modern gradient dark blue to purple)
            'hex_background_color' => '#1e3a8a', // Deep blue
            'hex_foreground_color' => '#ffffff',
            
            // Logo URL - Premium quality logo
            'logo_url' => env('GOOGLE_WALLET_LOGO_URL', 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=400&h=400&fit=crop'),
            'logo_description' => 'Apple Account Premium Logo',
            
            // Hero Image - Professional modern business/tech theme
            'hero_image_url' => env('GOOGLE_WALLET_HERO_IMAGE_URL', 'https://images.unsplash.com/photo-1557683316-973673baf926?w=1200&h=600&fit=crop'),
            'hero_image_description' => 'Premium Member Hero Image',
            
            // Wide Image - Additional visual element
            'wide_image_url' => env('GOOGLE_WALLET_WIDE_IMAGE_URL', 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=1200&h=300&fit=crop'),
            'wide_image_description' => 'Wide Banner Image',
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
