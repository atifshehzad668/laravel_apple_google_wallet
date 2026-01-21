@extends('layouts.app')

@section('title', 'Member Registration')

@section('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        color: #333;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 16px;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .error {
        color: #ef4444;
        font-size: 14px;
        margin-top: 4px;
    }

    .success-message {
        background: #d1fae5;
        color: #065f46;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border: 2px solid #6ee7b7;
    }

    .success-message h3 {
        margin-bottom: 10px;
    }

    .wallet-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .wallet-btn {
        flex: 1;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        font-weight: 600;
        transition: transform 0.2s;
    }

    .wallet-btn:hover {
        transform: translateY(-2px);
    }

    .apple-wallet {
        background: #000;
        color: #fff;
    }

    .google-wallet {
        background: #4285f4;
        color: #fff;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="logo">
            <h1>{{ config('wallet.branding.organization_name') }}</h1>
            <p>{{ config('wallet.branding.tagline') }}</p>
        </div>

        <div id="success-message" style="display: none;" class="success-message">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 60px; margin-bottom: 15px;">✅</div>
                <h3 style="font-size: 24px; color: #065f46;">Registration Successful!</h3>
                <p style="font-size: 16px; opacity: 0.9;">Welcome to {{ config('wallet.branding.organization_name') }}! Your account has been created and your membership passes are ready.</p>
            </div>

            <div style="background: rgba(255,255,255,0.5); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin: 0;"><strong>Member ID:</strong> <span id="member-id" style="font-family: monospace; letter-spacing: 1px;"></span></p>
                <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.7;">An email has been sent to your address with these details.</p>
            </div>

            <div class="wallet-buttons" id="wallet-buttons" style="display: none; flex-direction: column; gap: 12px;">
                <a href="#" id="apple-wallet-link" class="wallet-btn apple-wallet" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" width="20" style="filter: invert(1);"> Add to Apple Wallet
                </a>
                <a href="#" id="google-wallet-link" class="wallet-btn google-wallet" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_Pay_Logo.svg" width="40" style="filter: brightness(0) invert(1);"> Add to Google Wallet
                </a>
            </div>
        </div>

        <form id="registration-form">
            @csrf
            
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
                <div class="error" id="error-first_name"></div>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
                <div class="error" id="error-last_name"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" required>
                <div class="error" id="error-email"></div>
            </div>

            <div class="form-group">
                <label for="mobile">Mobile Number *</label>
                <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="+1 (555) 123-4567" required>
                <div class="error" id="error-mobile"></div>
            </div>

            <button type="submit" id="submit-btn" class="btn">Register Now</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('registration-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submit-btn');
    const form = e.target;
    
    // Clear previous errors
    document.querySelectorAll('.error').forEach(el => el.textContent = '');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Registering...';
    
    try {
        const formData = new FormData(form);
        const response = await fetch('{{ route("register") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Hide form
            form.style.display = 'none';
            
            // Show success message
            const successMsg = document.getElementById('success-message');
            successMsg.style.display = 'block';
            document.getElementById('member-id').textContent = data.data.unique_member_id;
            
            // Show wallet buttons if URLs are available
            const walletButtons = document.getElementById('wallet-buttons');
            if (data.data.apple_pass_url || data.data.google_pass_url) {
                walletButtons.style.display = 'flex';
                
                if (data.data.apple_pass_url) {
                    document.getElementById('apple-wallet-link').href = data.data.apple_pass_url;
                } else {
                    document.getElementById('apple-wallet-link').style.display = 'none';
                }
                
                if (data.data.google_pass_url) {
                    document.getElementById('google-wallet-link').href = data.data.google_pass_url;
                } else {
                    document.getElementById('google-wallet-link').style.display = 'none';
                }
            }
            
            // Scroll to top
            window.scrollTo(0, 0);
        } else {
            // Show validation errors
            if (data.errors) {
                for (const [field, messages] of Object.entries(data.errors)) {
                    const errorEl = document.getElementById(`error-${field}`);
                    if (errorEl) {
                        errorEl.textContent = messages[0];
                    }
                }
            } else {
                alert(data.message || 'Registration failed. Please try again.');
            }
            
            submitBtn.disabled = false;
            submitBtn.textContent = 'Register Now';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Register Now';
    }
});
</script>
@endsection
