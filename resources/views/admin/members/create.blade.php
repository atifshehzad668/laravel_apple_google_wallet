@extends('layouts.admin')

@section('title', 'Add New Member')

@section('styles')
<style>
    .form-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        max-width: 600px;
        margin: 20px auto;
    }

    .form-header {
        margin-bottom: 25px;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: #667eea;
        outline: none;
    }

    .btn-row {
        display: flex;
        gap: 12px;
        margin-top: 30px;
    }

    .btn {
        padding: 11px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 15px;
        text-decoration: none;
        display: inline-block;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }

    .error-text {
        color: #ef4444;
        font-size: 13px;
        margin-top: 4px;
    }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-header">
        <h2 style="margin: 0; color: #111827;">Add New Member</h2>
        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">Create a new member and generate their wallet passes.</p>
    </div>

    <form action="{{ route('admin.members.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}" required>
                @error('first_name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required>
                @error('last_name') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="mobile">Mobile Number</label>
            <input type="text" name="mobile" id="mobile" class="form-control" placeholder="+1 (555) 000-0000" value="{{ old('mobile') }}" required>
            @error('mobile') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">Create Member</button>
            <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
