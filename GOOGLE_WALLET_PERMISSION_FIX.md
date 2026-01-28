# Google Wallet Permission Fix

## Error You're Seeing

```
Service account email address did not have edit access on the issuer.
```

## Problem

Your service account (`apple-account@apple-accounts-485214.iam.gserviceaccount.com`) doesn't have the correct permissions to create/manage passes for your issuer ID (`3388000000022345850`).

## Solution: Add Service Account to Google Pay & Wallet Console

### Step 1: Go to Google Pay & Wallet Console

1. Visit [Google Pay & Wallet Console](https://pay.google.com/business/console)
2. Sign in with your Google account (the one that owns the project `apple-accounts-485214`)

### Step 2: Navigate to Issuer Settings

1. Click on **"Google Wallet API"** in the left sidebar
2. Click on **"Issuers"** or find your issuer ID: `3388000000022345850`

### Step 3: Add Service Account

1. Look for **"Service account users"** or **"Users"** section
2. Click **"Add User"** or **"Add Service Account"**
3. Enter the service account email: `apple-account@apple-accounts-485214.iam.gserviceaccount.com`
4. Set the role/permission to: **"Developer"** or **"Admin"**
5. Click **"Save"** or **"Add"**

### Alternative: Using Google Cloud Console

If you can't find the issuer settings in the Wallet Console:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select project: `apple-accounts-485214`
3. Navigate to **APIs & Services** → **Credentials**
4. Find your service account: `apple-account@apple-accounts-485214.iam.gserviceaccount.com`
5. Ensure it has the role: **"Wallet Objects Admin"** or **"Owner"**

### Step 4: Verify API is Enabled

1. In Google Cloud Console, go to **APIs & Services** → **Enabled APIs**
2. Make sure **"Google Wallet API"** is enabled
3. If not, click **"+ ENABLE APIS AND SERVICES"** and search for "Google Wallet API"

### Step 5: Wait and Test (Important!)

After adding permissions:
- **Wait 5-10 minutes** for changes to propagate
- Clear your browser cache
- Try creating the pass again

## Quick Test After Fix

```php
// Test in tinker or create a test route
php artisan tinker

$service = new \App\Services\GenericPassService();
$passData = $service->generatePass(1); // Use a real member ID
dd($passData);
```

## Common Issues

### Issue 1: Wrong Service Account Email
**Check**: Make sure you're using the exact email from your service account file
```json
"client_email": "apple-account@apple-accounts-485214.iam.gserviceaccount.com"
```

### Issue 2: Issuer ID Mismatch
**Check your `.env` or `config/wallet.php`:**
```
GOOGLE_WALLET_ISSUER_ID=3388000000022345850
```

### Issue 3: Service Account Key Invalid
**Verify** your `storage/app/google/apple-accounts.json` file:
- Check it has a valid `private_key`
- Ensure the file is readable by your web server

## Still Not Working?

### Create a New Issuer ID

If you can't access the current issuer, you can create a new one:

1. Go to [Google Pay & Wallet Console](https://pay.google.com/business/console)
2. Navigate to **"Google Wallet API"** → **"Issuers"**
3. Click **"Create Issuer"**
4. Enter your business details
5. Copy the new issuer ID (format: `3388000000022XXXXXX`)
6. Update your configuration:

```env
# In .env file
GOOGLE_WALLET_ISSUER_ID=3388000000022XXXXXX
```

```php
// In config/wallet.php
'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID', '3388000000022XXXXXX'),
```

7. The new issuer will automatically have access for your service account

## Verification Checklist

- [ ] Service account email is correct
- [ ] Service account has "Developer" or "Admin" role on issuer
- [ ] Google Wallet API is enabled in Google Cloud Console
- [ ] Issuer ID matches in your configuration
- [ ] Waited 5-10 minutes after making changes
- [ ] Tested with a simple pass generation

## Next Steps After Fix

Once permissions are working:

1. Test pass generation: `$service->generatePass($memberId)`
2. Check the pass appears in Google Wallet
3. Verify QR code and member information are correct
4. Integrate into your member creation workflow

## Support Links

- [Google Wallet API Documentation](https://developers.google.com/wallet)
- [Google Pay & Wallet Console](https://pay.google.com/business/console)
- [Google Cloud Console](https://console.cloud.google.com/)
- [Service Account Permissions Guide](https://developers.google.com/wallet/generic/web/prerequisites)
