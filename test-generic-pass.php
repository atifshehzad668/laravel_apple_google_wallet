<?php

/**
 * Test Script for Generic Pass Generation
 * 
 * This script tests the Generic Pass integration with your member data.
 * Run with: php test-generic-pass.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\GenericPassService;
use App\Models\Member;

echo "\n";
echo "========================================\n";
echo "  Generic Pass Generation Test\n";
echo "========================================\n\n";

// Get a member to test with
$member = Member::first();

if (!$member) {
    echo "❌ No members found in database!\n";
    echo "   Please create a member first.\n\n";
    exit(1);
}

echo "Testing with Member:\n";
echo "  ID: {$member->id}\n";
echo "  Name: {$member->full_name}\n";
echo "  Email: {$member->email}\n";
echo "  Member ID: {$member->unique_member_id}\n\n";

echo "Configuration Check:\n";
echo "  Issuer ID: " . config('wallet.google.issuer_id') . "\n";
echo "  Class ID: " . config('wallet.google.class_id') . "\n";
echo "  Service Account: " . config('wallet.google.service_account_file') . "\n";
echo "  Service Account Exists: " . (file_exists(config('wallet.google.service_account_file')) ? '✅ Yes' : '❌ No') . "\n";
echo "  Logo URL: " . config('wallet.google.design.logo_url') . "\n";
echo "  Hero Image URL: " . config('wallet.google.design.hero_image_url') . "\n\n";

echo "Generating pass...\n";

try {
    $service = new GenericPassService();
    $passData = $service->generatePass($member->id);
    
    echo "✅ Pass generated successfully!\n\n";
    
    echo "Pass Details:\n";
    echo "  Object ID: {$passData['object_id']}\n";
    echo "  Pass URL: {$passData['pass_url']}\n";
    echo "  Member ID: {$passData['member_id']}\n";
    echo "  Member Name: {$passData['member_name']}\n\n";
    
    echo "Add to Google Wallet URL:\n";
    echo "  {$passData['pass_url']}\n\n";
    
    echo "========================================\n";
    echo "  Test Completed Successfully! ✅\n";
    echo "========================================\n\n";
    
    echo "Next Steps:\n";
    echo "  1. Copy the URL above and paste in your browser\n";
    echo "  2. Click 'Add to Google Wallet'\n";
    echo "  3. Verify the pass appears with:\n";
    echo "     - Member name: {$member->full_name}\n";
    echo "     - Member ID: {$member->unique_member_id}\n";
    echo "     - Email: {$member->email}\n";
    echo "     - QR Code with member ID\n";
    echo "     - Hero image\n\n";
    
} catch (Exception $e) {
    echo "❌ Error generating pass!\n\n";
    echo "Error Message:\n";
    echo "  " . $e->getMessage() . "\n\n";
    echo "Error Trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    
    echo "Troubleshooting:\n";
    echo "  1. Check that service account has Developer permission\n";
    echo "  2. Verify issuer ID is correct: 3388000000023068687\n";
    echo "  3. Ensure class ID is correct: appleaccounts1234\n";
    echo "  4. Check storage/logs/laravel.log for details\n\n";
    
    exit(1);
}
