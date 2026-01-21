@extends('layouts.admin')

@section('title', 'Google Wallet Pass')

@section('styles')
<style>
    .pass-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 400px;
    }

    .pass-card {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 48px;
        width: 100%;
        max-width: 800px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .pass-title {
        font-size: 20px;
        color: #374151;
        margin-bottom: 48px;
        font-weight: 500;
        letter-spacing: -0.01em;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .google-wallet-button {
        display: inline-flex;
        align-items: center;
        background-color: #1e1e1e;
        color: #ffffff;
        padding: 0 24px;
        height: 52px;
        border-radius: 26px;
        text-decoration: none;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-weight: 500;
        font-size: 18px;
        transition: background-color 0.2s, transform 0.1s;
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        cursor: pointer;
    }

    .google-wallet-button:hover {
        background-color: #000000;
        transform: translateY(-1px);
        color: #ffffff;
        text-decoration: none;
    }

    .google-wallet-button:active {
        background-color: #333333;
        transform: translateY(0);
    }

    .google-wallet-button:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
        transform: none;
    }

    .google-wallet-icon {
        width: 32px;
        height: 32px;
        margin-right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 6px;
        padding: 4px;
    }

    .google-wallet-icon svg {
        width: 100%;
        height: 100%;
    }

    .loader {
        margin-left: 10px;
        width: 20px;
        height: 20px;
        border: 2px solid #FFF;
        border-bottom-color: transparent;
        border-radius: 50%;
        display: none;
        animation: rotation 1s linear infinite;
    }

    @keyframes rotation {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="pass-container">
    <div class="pass-card">
        <h2 class="pass-title">Download Your Google Pass</h2>
        
        <button id="add-to-wallet" class="google-wallet-button">
            <span class="google-wallet-icon">
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M44 24C44 12.95 35.05 4 24 4S4 12.95 4 24s8.95 20 20 20 20-8.95 20-20z" fill="#f8f9fa"/>
                    <path d="M34 18H14c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V20c0-1.1-.9-2-2-2zm-2 10H16v-6h16v6z" fill="#1a73e8"/>
                    <path d="M16 20h16v2H16z" fill="#ea4335"/>
                    <path d="M16 24h10v2H16z" fill="#fbbc04"/>
                    <path d="M16 28h16v2H16z" fill="#34a853"/>
                </svg>
            </span>
            Add to Google Wallet
            <span class="loader"></span>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#add-to-wallet').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $loader = $btn.find('.loader');
            
            $btn.prop('disabled', true);
            $loader.show();

            $.ajax({
                url: "{{ route('google.wallet.redirect') }}",
                method: 'GET',
                success: function(response) {
                    // Assuming the response will be a URL string or an object with a URL
                    if (typeof response === 'string') {
                        window.location.href = response;
                    } else if (response.url) {
                        window.location.href = response.url;
                    } else {
                        console.log('Response:', response);
                        // If dd() is used, response might be HTML. 
                        // In production, we'd expect a JSON redirect URL.
                    }
                },
                error: function(xhr) {
                    alert('Error generating pass. Please try again.');
                    console.error('AJAX Error:', xhr);
                    $btn.prop('disabled', false);
                    $loader.hide();
                }
            });
        });
    });
</script>
@endsection
