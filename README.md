# Laravel Wallet System - Membership Management with Apple & Google Wallet Integration

A complete membership management system built with Laravel 12, featuring seamless Apple Wallet and Google Wallet integration. Members can register online and instantly receive digital membership cards to their mobile wallets.

## ✨ Features

- **🎨 Modern UI**: Premium glassmorphism design with smooth animations
- **📱 Dual Wallet Support**: Apple Wallet (.pkpass) and Google Wallet integration
- **📧 Automated Emails**: Welcome emails with wallet links sent via SMTP
- **🔐 Admin Dashboard**: Full member management with search, filtering, and analytics
- **📊 Real-time Stats**: Dashboard with member counts and registration trends
- **🎫 QR Code Barcodes**: Unique member IDs encoded for onsite scanning
- **♻️ Pass Regeneration**: Admins can regenerate and resend wallet passes
- **🔒 Secure Authentication**: BCrypt password hashing and session management
- **📝 Activity Logging**: Complete audit trail of admin actions
- **👤 Admin Profile**: Profile management and password change functionality

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 12.x
- **MySQL**: 8.0 or higher
- **Composer**: Latest version
- **SMTP Server**: Gmail, SendGrid, Mailgun, or your hosting provider

### For Apple Wallet

- Apple Developer Account
- Pass Type ID Certificate (.p12 file)
- Apple WWDR Certificate

### For Google Wallet

- Google Cloud Project
- Google Wallet API enabled
- Service Account with JSON key file

## 🚀 Quick Start

### 1. Install Dependencies

```bash
cd d:\wamp64\www\wallet_system\laravel_google_apple_wallet

# Install PHP dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_system_laravel
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create database:

```sql
CREATE DATABASE wallet_system_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations:

```bash
php artisan migrate
```

### 3. Configure Mail

Edit `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### 4. Configure Wallet Credentials

Edit `.env` with your wallet credentials and branding information.

### 5. Set Up Storage

```bash
# Create storage link
php artisan storage:link

# Create required directories
mkdir storage\app\certificates
mkdir storage\app\passes\apple
mkdir storage\app\passes\google
mkdir storage\app\templates\apple-pass
```

Copy certificates from original PHP project to `storage/app/certificates/`.

### 6. Start Application

```bash
php artisan serve
```

Visit `http://localhost:8000` for member registration or `http://localhost:8000/admin` for admin panel.

**Default Admin Credentials**:
- Username: `admin`
- Password: `admin123` (⚠️ Change this immediately!)

## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── MemberController.php          # Public registration
│   │   ├── PassController.php            # Wallet pass downloads
│   │   └── Admin/
│   │       ├── AuthController.php        # Admin auth & profile
│   │       ├── DashboardController.php   # Dashboard stats
│   │       └── MemberController.php      # Member management
│   └── Middleware/
│       └── AdminAuthenticate.php         # Admin auth middleware
├── Models/
│   ├── Member.php                        # Member model
│   ├── WalletPass.php                    # Wallet pass tracking
│   ├── AdminUser.php                     # Admin authentication
│   ├── EmailLog.php                      # Email delivery logs
│   └── ActivityLog.php                   # Admin activity logs
└── Services/
    ├── AppleWalletService.php            # Apple Wallet pass generation
    ├── GoogleWalletService.php           # Google Wallet pass generation
    ├── MemberService.php                 # Member business logic
    └── EmailNotificationService.php      # Email sending

config/
├── wallet.php                             # Wallet configuration
└── auth.php                               # Authentication (modified)

database/migrations/
├── 2024_01_01_000001_create_members_table.php
├── 2024_01_01_000002_create_wallet_passes_table.php
├── 2024_01_01_000003_create_admin_users_table.php
├── 2024_01_01_000004_create_email_logs_table.php
└── 2024_01_01_000005_create_activity_logs_table.php

resources/views/
├── layouts/
│   ├── app.blade.php                     # Public layout
│   └── admin.blade.php                   # Admin layout
├── register.blade.php                     # Member registration form
├── admin/
│   ├── login.blade.php                   # Admin login
│   ├── dashboard.blade.php               # Admin dashboard
│   ├── profile.blade.php                 # Admin profile
│   └── members/
│       └── index.blade.php               # Members management
└── emails/
    ├── membership-welcome.blade.php      # Welcome email
    └── pass-regeneration.blade.php       # Pass regeneration email
```

## 🎯 Usage

### Public Registration

1. Navigate to `http://localhost:8000/`
2. Fill out registration form
3. System generates unique member ID
4. Creates Apple & Google Wallet passes
5. Sends welcome email with wallet links

### Admin Panel

Access at `http://localhost:8000/admin`

**Dashboard Features**:
- Total members, active members
- Today's registrations, weekly/monthly stats
- Recent member list
- Activity logs

**Member Management**:
- Search by name, email, or member ID
- Filter by status (active/inactive)
- Regenerate wallet passes
- Delete members
- Complete activity tracking

**Profile Management**:
- Update name and email
- Change password
- View account details

## 🔧 Configuration

All configuration is centralized in `config/wallet.php`:

- **Apple Wallet**: Team ID, Pass Type ID, certificates
- **Google Wallet**: Issuer ID, Service Account
- **Branding**: Organization name, colors, contact info

Environment variables in `.env` allow easy deployment configuration.

## 🔒 Security

- ✅ BCrypt password hashing
- ✅ CSRF protection
- ✅ SQL injection protection (prepared statements)
- ✅ Input validation and sanitization
- ✅ Session-based authentication
- ✅ Custom admin guard
- ✅ Activity logging for audits

## 📝 API Endpoints

### Public
- `POST /register` - Register new member
- `GET /pass/download/{id}` - Download Apple Wallet pass

### Admin (Protected)
- `POST /admin/login` - Admin login
- `POST /admin/logout` - Admin logout
- `GET /admin/dashboard` - Dashboard
- `GET /admin/members` - Member list
- `POST /admin/members/regenerate-pass` - Regenerate pass
- `POST /admin/members/delete` - Delete member
- `GET /admin/profile` - View profile
- `POST /admin/profile` - Update profile
- `POST /admin/profile/password` - Change password

## 🐛 Troubleshooting

### Database Connection Issues
- Verify MySQL is running in WAMP
- Check credentials in `.env`
- Ensure database exists

### Email Not Sending
- Check SMTP credentials
- For Gmail, use App Password
- Check `email_logs` table
- Test with `MAIL_MAILER=log` first

### Certificate Errors
- Ensure certificates are in `storage/app/certificates/`
- Verify file permissions
- Check paths in `.env`

## 📚 Documentation

For detailed setup instructions and troubleshooting, see the [Walkthrough Guide](../../../.gemini/antigravity/brain/7ffffa89-de0f-4f6e-875c-26d5241222fd/walkthrough.md).

For implementation details, see the [Implementation Plan](../../../.gemini/antigravity/brain/7ffffa89-de0f-4f6e-875c-26d5241222fd/implementation_plan.md).

## 🎉 What's Included

✅ Complete Laravel backend implementation
✅ Database migrations with seed data
✅ Eloquent models with relationships
✅ Service layer for business logic
✅ Apple Wallet pass generation
✅ Google Wallet JWT integration
✅ Email notifications
✅ Admin authentication system
✅ Activity logging
✅ Modern UI views (Blade templates)
✅ Responsive design
✅ Complete documentation

## 📄 License

This project is provided as-is for your use. Feel free to modify and customize for your organization.

## 💡 Future Enhancements

- Membership tiers (Gold, Silver, Bronze)
- Payment processing integration
- Pass expiration dates
- Push notifications for pass updates
- Member portal for self-service
- CSV/Excel export functionality
- Multi-language support
- Email templates customization

---

**Built with ❤️ using Laravel 12 for seamless member onboarding**
# laravel_apple_google_wallet
