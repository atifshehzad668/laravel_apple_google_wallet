<?php

/**
 * Test script to verify Apple Wallet certificate
 */

$certPath = __DIR__ . '/storage/app/apple/PinkRoomPass.p12';
$passwords = ['', 'password', 'pinkroom', 'PinkRoom', 'pink123'];

echo "Testing certificate: {$certPath}\n";
echo str_repeat('=', 60) . "\n\n";

if (!file_exists($certPath)) {
    echo "❌ ERROR: Certificate file not found!\n";
    exit(1);
}

echo "✅ Certificate file exists (" . filesize($certPath) . " bytes)\n\n";

foreach ($passwords as $index => $password) {
    $displayPassword = $password === '' ? '(empty/blank)' : $password;
    echo "Testing password #{$index}: {$displayPassword}\n";
    
    $certs = [];
    $result = openssl_pkcs12_read(file_get_contents($certPath), $certs, $password);
    
    if ($result) {
        echo "✅ SUCCESS! Certificate unlocked with password: {$displayPassword}\n";
        echo str_repeat('=', 60) . "\n";
        echo "Certificate Details:\n";
        
        if (isset($certs['cert'])) {
            $certData = openssl_x509_parse($certs['cert']);
            echo "  - Subject: " . ($certData['subject']['CN'] ?? 'N/A') . "\n";
            echo "  - Issuer: " . ($certData['issuer']['CN'] ?? 'N/A') . "\n";
            echo "  - Valid From: " . date('Y-m-d', $certData['validFrom_time_t']) . "\n";
            echo "  - Valid Until: " . date('Y-m-d', $certData['validTo_time_t']) . "\n";
        }
        
        echo "\n🎉 This is the correct password!\n";
        echo "Update your .env file with:\n";
        echo "APPLE_WALLET_CERT_PASSWORD={$password}\n";
        exit(0);
    }
    
    echo "❌ Failed - Incorrect password\n\n";
}

echo str_repeat('=', 60) . "\n";
echo "⚠️  None of the common passwords worked.\n";
echo "You need to ask Timothy McGrath for the certificate password.\n";
