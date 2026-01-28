# Ready to Test! 🚀

## Configuration Updated

I've updated your Generic Pass integration with the correct configuration:

### ✅ Configuration Details
- **Issuer ID**: `3388000000023068687`
- **Class ID**: `appleaccounts1234`  
- **Service Account**: `apple-account@apple-accounts-485214.iam.gserviceaccount.com` (has Developer permission ✓)
- **Service Account File**: `storage/app/google/apple-accounts.json`
- **Hero Image**: `https://thumbs.dreamstime.com/b/beautiful-rain-forest-ang-ka-nature-trail-doi-inthanon-national-park-thailand-36703721.jpg`
- **Logo**: `https://lh3.googleusercontent.com/W5_s9Uu6ttIJpdT5YkDfoLh4GSfN8fhgL27Hd4vn-6lxmWAewpcG37tVYEo9uV8_OaQ4HpRNoTsfn52HNvugjjBwiQ=s925`

### 📝 Files Updated
1. ✅ `config/wallet.php` - Updated with correct IDs and hero image support
2. ✅ `app/Lib/GenericPass.php` - Added hero image support
3. ✅ `.env` - Added hero image URL
4. ✅ Created `test-generic-pass.php` - Test script

## 🧪 Test Now

Run this command to test pass generation:

```bash
php test-generic-pass.php
```

This will:
- ✓ Check your configuration
- ✓ Verify service account file exists
- ✓ Generate a pass for the first member in your database
- ✓ Display the "Add to Google Wallet" URL
- ✓ Show detailed error messages if something fails

## 🎯 Expected Result

You should see output like:

```
========================================
  Generic Pass Generation Test
========================================

Testing with Member:
  ID: 1
  Name: John Doe
  Email: john@example.com
  Member ID: PMC-2024-001

Configuration Check:
  Issuer ID: 3388000000023068687
  Class ID: appleaccounts1234
  Service Account: .../apple-accounts.json
  Service Account Exists: ✅ Yes
  Logo URL: https://lh3.googleusercontent.com/...
  Hero Image URL: https://thumbs.dreamstime.com/...

Generating pass...
✅ Pass generated successfully!

Pass Details:
  Object ID: 3388000000023068687.PMC_2024_001
  Pass URL: https://pay.google.com/gp/v/save/...

Add to Google Wallet URL:
  https://pay.google.com/gp/v/save/eyJ0eXAiOiJKV1Q...

========================================
  Test Completed Successfully! ✅
========================================
```

## 🌐 Testing in Browser

1. Copy the "Add to Google Wallet URL" from the test output
2. Paste it in your browser
3. Click "Add to Google Wallet"
4. Verify the pass shows:
   - ✓ Member name
   - ✓ Member ID  
   - ✓ Email address
   - ✓ Mobile number
   - ✓ QR code
   - ✓ Hero image (forest image)
   - ✓ Apple Account logo
   - ✓ Links to website and support

## ❌ Troubleshooting

If you see errors:

### Error: "Service account does not have edit access"
- Wait 5-10 minutes (permissions may still be propagating)
- Verify in Google Pay Console that service account has Developer role

### Error: "Class not found"
- The test script will automatically create the class
- Make sure issuer ID is correct: `3388000000023068687`

### Error: "Service account file not found"
- Check `storage/app/google/apple-accounts.json` exists
- Verify file permissions are readable

### Error: "Invalid member ID"
- Make sure you have at least one member in your database
- Create a test member if needed

## 📧 Integration

Once testing works, integrate into your app:

### Option 1: Controller Route
```php
Route::get('/member/{id}/google-wallet', function($id) {
    $service = new \App\Services\GenericPassService();
    $passData = $service->generatePass($id);
    return redirect($passData['pass_url']);
});
```

### Option 2: API Endpoint
```php
Route::post('/api/generate-pass', function(Request $request) {
    $service = new \App\Services\GenericPassService();
    $passData = $service->generatePass($request->member_id);
    return response()->json($passData);
});
```

### Option 3: Member Welcome Email
```php
$service = new \App\Services\GenericPassService();
$passUrl = $service->getPassUrl($member->id);
// Include $passUrl in your email template
```

## 🎉 What's Included in Your Pass

The Generic Pass will automatically include:

| Field | Value |
|-------|-------|
| **Card Title** | "Apple Account Pass" |
| **Header** | Member's full name |
| **Member ID** | Unique member identifier |
| **Email** | Member's email |
| **Mobile** | Member's phone number |
| **Member Since** | Join date |
| **QR Code** | Member ID (scannable) |
| **Hero Image** | Forest image you provided |
| **Logo** | Apple Account logo |
| **Links** | Website & Support email |

All data is pulled automatically from your Member model!

---

**Ready? Run the test!**

```bash
php test-generic-pass.php
```
