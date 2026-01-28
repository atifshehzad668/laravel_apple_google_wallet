# Generic Pass Integration - Quick Start Guide

## What I Created for You

I've built a complete **Generic Google Wallet Pass** integration system for your Laravel application that replaces the EventTicketPass with a more suitable member pass system.

## 📁 Files Created

### 1. Core Service Layer
- **`app/Services/GenericPassService.php`** - Main service for managing generic passes
  - Integrates with your Member model
  - Handles pass generation, regeneration, and revocation
  - Manages database persistence
  - Uses the service account key from `storage/app/google/apple-accounts.json`

### 2. Enhanced Library
- **`app/Lib/GenericPass.php`** *(Updated)*
  - Added mobile number support
  - Added conditional field handling
  - Added links module for website and support
  - Better suited for membership cards

### 3. Controller
- **`app/Http/Controllers/GenericPassController.php`**
  - Ready-to-use HTTP endpoints
  - JSON API responses
  - Error handling and logging

### 4. Documentation
- **`GENERIC_PASS_INTEGRATION.md`** - Complete integration guide
- **`routes/generic-pass-routes-example.php`** - Example routes
- **`app/Examples/GenericPassMigrationGuide.php`** - Migration guide from EventTicketPass

## 🚀 Quick Start

### Step 1: Generate a Pass

```php
use App\Services\GenericPassService;

$service = new GenericPassService();
$passData = $service->generatePass($memberId);

// Returns:
// [
//     'object_id' => '3388000000022345850.MEMBER_123',
//     'pass_url' => 'https://pay.google.com/gp/v/save/...',
//     'member_id' => 123,
//     'member_name' => 'John Doe'
// ]
```

### Step 2: Add Routes

Copy from `routes/generic-pass-routes-example.php` to your `routes/web.php`:

```php
use App\Http\Controllers\GenericPassController;

Route::get('/google-wallet/generic/{memberId}', 
    [GenericPassController::class, 'downloadGooglePass'])
    ->name('google.wallet.generic.download');
```

### Step 3: Use in Your Blade

```blade
<a href="{{ route('google.wallet.generic.download', ['memberId' => $member->id]) }}" 
   class="btn btn-primary">
    Add to Google Wallet
</a>
```

## 🔑 Key Features

### Pass Information
The generic pass automatically includes:
- ✅ Member's full name (header)
- ✅ Member ID
- ✅ Email address
- ✅ Mobile number
- ✅ Member since date
- ✅ QR code with member ID
- ✅ Links to website and support

### Service Operations
- `generatePass($memberId)` - Create new pass
- `regeneratePass($memberId)` - Regenerate existing pass
- `revokePass($memberId)` - Expire/revoke pass
- `getPassUrl($memberId)` - Get "Add to Wallet" URL
- `hasActivePass($memberId)` - Check if member has active pass
- `getAllActivePasses()` - Get all active passes

## 🔧 Configuration

The service uses:
- **Service Account Key**: `storage/app/google/apple-accounts.json` ✅ (Already detected)
- **Configuration**: `config/wallet.php`
- **Branding**: `config/wallet.branding`

## 📊 Comparison with EventTicketPass

| Feature | EventTicketPass | GenericPass |
|---------|----------------|-------------|
| Use Case | Events/Tickets | Membership Cards |
| Fields | Seat, Row, Gate | Member Info, QR Code |
| Customization | Limited | Full (colors, logo, fields) |
| Integration | Manual | Automatic via Service |
| Pass Type | eventticketobject | genericobject |

## 💡 Common Use Cases

### 1. Auto-generate on Member Creation
```php
// In MemberController
$member = Member::create($data);
$passData = app(GenericPassService::class)->generatePass($member->id);
```

### 2. Include in Welcome Email
```php
$passUrl = app(GenericPassService::class)->getPassUrl($member->id);
// Pass $passUrl to email template
```

### 3. Revoke on Member Deletion
```php
// In Member model boot method
static::deleting(function ($member) {
    app(GenericPassService::class)->revokePass($member->id);
});
```

## 📖 Full Documentation

See `GENERIC_PASS_INTEGRATION.md` for:
- Complete API reference
- Advanced usage examples
- Email integration
- Troubleshooting guide
- Security notes

## 🎯 Next Steps

1. **Test the integration**:
   ```php
   $service = new \App\Services\GenericPassService();
   $passData = $service->generatePass(1); // Replace 1 with a real member ID
   dd($passData);
   ```

2. **Add routes** from `routes/generic-pass-routes-example.php`

3. **Update your member creation flow** to generate passes

4. **Update email templates** to include "Add to Google Wallet" buttons

## ⚠️ Important Notes

- ✅ Service account key is already configured at `storage/app/google/apple-accounts.json`
- ✅ Uses your existing issuer ID: `3388000000022345850`
- ✅ Automatically saves to `wallet_passes` table
- ✅ Integrates with existing Member model

## 🆘 Support

If you encounter any issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify service account key exists and is readable
3. Ensure Google Wallet API is enabled in your Google Cloud project
4. See troubleshooting section in `GENERIC_PASS_INTEGRATION.md`

---

**Ready to use!** Start by testing the service with a real member ID, then integrate into your application workflow.
