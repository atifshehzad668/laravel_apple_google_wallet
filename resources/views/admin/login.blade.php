@extends('layouts.app')

@section('title', 'Admin Login')

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
        border-color: #1e3a8a;
    }

    .btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
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

    .error-message {
        background: #fee2e2;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #fca5a5;
    }

    .back-link {
        text-align: center;
        margin-top: 20px;
    }

    .back-link a {
        color: #667eea;
        text-decoration: none;
    }

    .back-link a:hover {
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="logo">
            <h1>🔐 Admin Login</h1>
            <p>{{ config('wallet.branding.organization_name') }}</p>
        </div>

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('error'))
            <div class="error-message">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</div>
@endsection
