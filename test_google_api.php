<?php
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Walletobjects;

$keyFile = 'storage/app/certificates/google-wallet-service-account.json';

if (!file_exists($keyFile)) {
    die("Key file not found at $keyFile\n");
}

try {
    $client = new Client();
    $client->setAuthConfig($keyFile);
    $client->addScope(Walletobjects::WALLET_OBJECT_ISSUER);
    
    // Attempt to get an access token - this triggers a network request to Google
    $token = $client->fetchAccessTokenWithAssertion();
    
    if (isset($token['access_token'])) {
        echo "SUCCESS: Connection to Google API established!\n";
    } else {
        echo "FAILURE: Could not get access token. Result: " . print_r($token, true) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
