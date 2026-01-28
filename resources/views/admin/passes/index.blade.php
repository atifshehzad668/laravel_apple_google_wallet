@extends('layouts.admin')

@section('title', 'Pass Gallery')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #8b5cf6 100%);
        --glass-bg: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.1);
        --card-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    body {
        font-family: 'Outfit', sans-serif;
    }

    .gallery-container {
        padding: 40px;
        background: #0f172a;
        min-height: 100vh;
        color: white;
    }

    .gallery-header h1 {
        font-size: 32px;
        font-weight: 700;
        background: linear-gradient(to right, #fff, #94a3b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 35px;
        padding-top: 20px;
    }

    /* Premium Wallet Card */
    .wallet-card {
        background: var(--primary-gradient);
        border-radius: 24px;
        height: 220px;
        padding: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid var(--glass-border);
    }

    .wallet-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('https://www.transparenttextures.com/patterns/stardust.png');
        opacity: 0.1;
        pointer-events: none;
    }

    .wallet-card:hover {
        transform: translateY(-10px) rotateX(10deg) rotateY(-5deg);
        box-shadow: 0 30px 60px rgba(59, 130, 246, 0.2);
    }

    .card-top {
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 5;
    }

    .card-logo {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 1);
        border-radius: 14px;
        padding: 8px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .member-info {
        margin-top: auto;
        z-index: 5;
    }

    .member-name {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: -0.5px;
    }

    .member-id {
        font-size: 13px;
        font-family: 'Monaco', 'Consolas', monospace;
        letter-spacing: 2px;
        opacity: 0.6;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        z-index: 5;
    }

    .card-type-tag {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Abstract Glass Elements */
    .glass-orb {
        position: absolute;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        top: -100px;
        right: -50px;
        z-index: 1;
    }

    /* Action Overlay */
    .card-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(12px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 15px;
        opacity: 0;
        transition: all 0.4s ease;
        z-index: 20;
    }

    .wallet-card:hover .card-overlay {
        opacity: 1;
    }

    .btn-action {
        width: 180px;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-view {
        background: white;
        color: #0f172a;
    }

    .btn-action:hover {
        transform: scale(1.05);
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 100px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 32px;
    }
</style>
@endsection

@section('content')
<div class="gallery-container">
    <div class="gallery-header">
        <div>
            <h1>Pass Gallery</h1>
            <p class="text-muted">Live view of all generated Google Wallet passes</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.members.index') }}" class="btn btn-primary">Manage Members</a>
        </div>
    </div>

    <div class="gallery-grid">
        @forelse($passes as $pass)
            <div class="wallet-card">
                <div class="glass-orb"></div>
                
                <div class="card-top">
                    <img src="{{ config('wallet.google.design.logo_url') }}" class="card-logo" alt="Logo">
                    <span class="card-title">{{ config('wallet.google.design.card_title') }}</span>
                </div>

                <div class="member-info">
                    <div class="member-name">{{ $pass->member->full_name }}</div>
                    <div class="member-id">{{ $pass->member->unique_member_id }}</div>
                </div>

                <div class="card-footer">
                    <span class="card-type-tag">Generic Membership</span>
                    <span style="font-size: 24px; opacity: 0.8;">💳</span>
                </div>

                <!-- Hover Actions -->
                <div class="card-overlay">
                    <a href="{{ $pass->google_pass_url }}" target="_blank" class="btn-action btn-view">
                        👁️ View Live Pass
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <span class="empty-icon">📇</span>
                <h3>No Passes Found</h3>
                <p class="text-muted">Once you generate Google Wallet passes for members, they will appear here as visual cards.</p>
                <a href="{{ route('admin.members.index') }}" class="btn btn-primary" style="margin-top: 15px;">Go to Members</a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
// Scripts can be added here if needed
</script>
@endsection
