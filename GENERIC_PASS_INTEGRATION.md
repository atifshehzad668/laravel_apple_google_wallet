# Generic Google Wallet Pass Integration

## Overview

This integration provides a complete solution for creating and managing **Generic Google Wallet passes** for members. It replaces the previous Event Ticket pass implementation with a more flexible generic pass system that's better suited for membership cards.

## Architecture

### Key Components

1. **`GenericPass.php`** (`app/Lib/GenericPass.php`)
   - Low-level library for interacting with Google Wallet API
   - Handles authentication, class creation, and object management
   - Based on Google's official Wallet API SDK

2. **`GenericPassService.php`** (`app/Services/GenericPassService.php`)
   - High-level service that integrates `GenericPass` with your member data
   - Manages pass generation, regeneration, revocation
   - Handles database persistence via `WalletPass` model

3. **`GenericPassController.php`** (`app/Http/Controllers/GenericPassController.php`)
   - HTTP endpoints for pass management
   - Provides JSON API and redirect functionality

### Service Account Configuration

The service uses the Google Cloud service account key located at:
```
storage/app/google/apple-accounts.json
```

This file contains:
- Project ID: `apple-accounts-485214`
- Service account credentials for authentication
- Private key for JWT signing

## Usage Examples

### 1. Generate a Pass for a Member

```php
use App\Services\GenericPassService;

$genericPassService = new GenericPassService();

// Generate pass for member ID 123
$passData = $genericPassService->generatePass(123);

// Returns:
// [
//     'object_id' => '3388000000022345850.MEMBER_123',
//     'pass_url' => 'https://pay.google.com/gp/v/save/...',
//     'member_id' => 123,
//     'member_name' => 'John Doe'
// ]
```

### 2. Get Pass URL for "Add to Google Wallet" Button

```php
$passUrl = $genericPassService->getPassUrl($memberId);

if ($passUrl) {
    echo "<a href='{$passUrl}' target='_blank'>Add to Google Wallet</a>";
}
```

### 3. Regenerate a Pass (e.g., after member data update)

```php
$passData = $genericPassService->regeneratePass($memberId);
```

### 4. Revoke/Expire a Pass (e.g., when member is deleted)

```php
$success = $genericPassService->revokePass($memberId);
```

### 5. Check if Member Has Active Pass

```php
if ($genericPassService->hasActivePass($memberId)) {
    echo "Member has an active wallet pass";
}
```

## API Endpoints

Add these routes to your `routes/web.php` or `routes/api.php`:

```php
use App\Http\Controllers\GenericPassController;

// Generate pass for a member
Route::post('/api/generic-pass/generate', [GenericPassController::class, 'generatePass']);

// Get pass URL for a member
Route::get('/api/generic-pass/{memberId}/url', [GenericPassController::class, 'getPassUrl']);

// Redirect to Google Wallet (for "Add to Wallet" button)
Route::get('/google-wallet/generic/{memberId}', [GenericPassController::class, 'downloadGooglePass'])
    ->name('google.wallet.generic');

// Regenerate pass
Route::post('/api/generic-pass/regenerate', [GenericPassController::class, 'regeneratePass']);

// Revoke pass
Route::post('/api/generic-pass/revoke', [GenericPassController::class, 'revokePass']);

// Check if member has active pass
Route::get('/api/generic-pass/{memberId}/check', [GenericPassController::class, 'checkActivePass']);

// Get all active passes
Route::get('/api/generic-pass/active', [GenericPassController::class, 'getAllActivePasses']);
```

## Pass Data Structure

The generic pass includes the following member information:

### Header
- **Card Title**: Configurable (e.g., "Member Card", "VIP Pass")
- **Header**: Member's full name
- **Subheader**: Optional (can be member ID)

### Barcode
- **Type**: QR Code
- **Value**: Member's unique ID

### Text Modules (Information Fields)
- **Member ID**: Unique member identifier
- **Email**: Member's email address
- **Mobile**: Member's mobile number (if provided)
- **Member Since**: Join date (formatted as "Month Day, Year")

### Links
- **Website**: Link to your organization's website
- **Support**: mailto: link to support email

### Design
- **Background Color**: Configurable (default: `#0f172a` - dark slate)
- **Logo**: Configurable URL for organization logo

## Configuration

Make sure your `config/wallet.php` includes:

```php
return [
    'google' => [
        'issuer_id' => '3388000000022345850',
        'class_id' => 'generic_member_pass',
        'service_account_file' => storage_path('app/google/apple-accounts.json'),
        'barcode_type' => 'QR_CODE',
        
        'design' => [
            'issuer_name' => 'Your Organization Name',
            'card_title' => 'Member Card',
            'hex_background_color' => '#0f172a',
            'logo_url' => 'https://yourdomain.com/logo.png',
            'logo_description' => 'Organization Logo',
        ],
    ],
    
    'branding' => [
        'website' => 'https://yourdomain.com',
        'support_email' => 'support@yourdomain.com',
    ],
];
```

## Integration with Member Creation

To automatically generate passes when creating members:

```php
// In your MemberController or Member Observer

use App\Services\GenericPassService;

public function store(Request $request)
{
    $member = Member::create($request->validated());
    
    // Generate Google Wallet pass
    $genericPassService = app(GenericPassService::class);
    $passData = $genericPassService->generatePass($member->id);
    
    return response()->json([
        'member' => $member,
        'google_wallet_url' => $passData['pass_url']
    ]);
}
```

## Email Integration

Include the pass URL in your welcome emails:

```blade
<!-- In your email template -->
<p>Welcome, {{ $member->full_name }}!</p>

@if($googleWalletUrl)
    <a href="{{ $googleWalletUrl }}" 
       style="background: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
        Add to Google Wallet
    </a>
@endif
```

In your mail class:

```php
use App\Services\GenericPassService;

public function build()
{
    $genericPassService = app(GenericPassService::class);
    $googleWalletUrl = $genericPassService->getPassUrl($this->member->id);
    
    return $this->view('emails.member-welcome')
                ->with('googleWalletUrl', $googleWalletUrl);
}
```

## Member Deletion Integration

Automatically revoke passes when members are deleted:

```php
// In App\Models\Member

protected static function booted()
{
    static::deleting(function ($member) {
        $genericPassService = app(GenericPassService::class);
        $genericPassService->revokePass($member->id);
    });
}
```

## Differences from EventTicketPass

| Feature | EventTicketPass | GenericPass |
|---------|----------------|-------------|
| Pass Type | Event Ticket | Generic |
| Use Case | Events, concerts | Membership, loyalty cards |
| Seat Info | Yes (gate, section, row) | No |
| Flexibility | Limited to event schema | Fully customizable |
| Design | Event-focused | Membership-focused |
| Background Color | Not customizable | Fully customizable |

## Troubleshooting

### Pass Not Generating
- Check that `storage/app/google/apple-accounts.json` exists and is readable
- Verify the service account has Google Wallet API enabled
- Check Laravel logs for detailed error messages

### Pass URL Invalid
- Ensure JWT signing is working correctly
- Verify issuer ID matches your Google Cloud project
- Check that the class was created successfully

### Pass Not Updating
- Remember: Google Wallet doesn't support direct object updates
- Use `regeneratePass()` to create a new pass with updated data
- Old passes are automatically expired

## Security Notes

1. **Service Account Key**: Keep `apple-accounts.json` secure and never commit to version control
2. **Access Control**: Add authentication to all pass management endpoints
3. **Rate Limiting**: Consider rate limiting pass generation to prevent abuse
4. **Validation**: Always validate member existence before generating passes

## Testing

```php
// Test pass generation
$service = new GenericPassService();
$passData = $service->generatePass(1);
dd($passData);

// Test pass retrieval
$url = $service->getPassUrl(1);
dd($url);

// Test pass revocation
$success = $service->revokePass(1);
dd($success);
```

## Support

For issues with:
- **Google Wallet API**: Check [Google Wallet API Documentation](https://developers.google.com/wallet)
- **Service Configuration**: Review your Google Cloud Console settings
- **Integration Issues**: Check Laravel logs at `storage/logs/laravel.log`
