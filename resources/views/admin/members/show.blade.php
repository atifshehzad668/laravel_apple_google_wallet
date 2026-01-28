@extends('layouts.admin')

@section('title', 'Member Details - ' . $member->full_name)

@section('styles')
<style>
    .detail-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 30px;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 24px;
        margin-bottom: 24px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-group {
        margin-bottom: 20px;
    }

    .info-label {
        font-size: 13px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 16px;
        color: #334155;
        font-weight: 500;
    }

    .wallet-preview {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        aspect-ratio: 1.58 / 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .wallet-preview-header {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        opacity: 0.9;
    }

    .wallet-preview-name {
        font-size: 24px;
        font-weight: bold;
        margin-top: 10px;
    }

    .wallet-preview-id {
        font-family: monospace;
        opacity: 0.8;
    }

    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 20px;
    }

    .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-google { background: #4285f4; color: white; }
    .btn-apple { background: #000000; color: white; }
    .btn-pdf { background: #0f172a; color: white; }
    .btn-outline { background: white; color: #1e293b; border: 1px solid #e2e8f0; }

    .btn:hover { opacity: 0.9; transform: translateY(-1px); }

    .badge {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-success { background: #dcfce7; color: #166534; }
    .badge-warning { background: #fef9c3; color: #854d0e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #e0f2fe; color: #075985; }
</style>
@endsection

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.members.index') }}" style="color: #64748b; text-decoration: none;">← Back to Members</a>
</div>

<div class="detail-container">
    <div class="main-content">
        <div class="card">
            <div class="card-title">
                Personal Information
                <span class="badge badge-{{ $member->status === 'active' ? 'success' : 'warning' }}">
                    {{ ucfirst($member->status) }}
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="info-group">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">{{ $member->full_name }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Member ID</div>
                    <div class="info-value">{{ $member->unique_member_id }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $member->email }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Mobile Number</div>
                    <div class="info-value">{{ $member->mobile }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Registered On</div>
                    <div class="info-value">{{ $member->created_at->format('F j, Y') }}</div>
                </div>
            </div>
        </div>

        @if($member->walletPass)
        <div class="card">
            <div class="card-title">Wallet Pass Status</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="info-group">
                    <div class="info-label">Google Object ID</div>
                    <div class="info-value">{{ $member->walletPass->google_object_id ?? 'Not Created' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Apple Serial Number</div>
                    <div class="info-value">{{ $member->walletPass->apple_serial_number ?? 'Not Created' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Google Pass Link</div>
                    <div class="info-value">
                        @if($member->walletPass->google_pass_url)
                            <a href="{{ $member->walletPass->google_pass_url }}" target="_blank" style="color: #4285f4; font-size: 14px; word-break: break-all;">Open in Browser</a>
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="info-group">
                    <div class="info-label">Pass Status</div>
                    <div class="info-value">
                        <select id="passStatusSelect" onchange="updatePassStatus({{ $member->id }}, this.value)" style="padding: 5px 10px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; font-weight: 600;">
                            <option value="active" {{ $member->walletPass->status === 'active' ? 'selected' : '' }}>🟢 Active</option>
                            <option value="pending" {{ $member->walletPass->status === 'pending' ? 'selected' : '' }}>🟡 Pending</option>
                            <option value="expired" {{ $member->walletPass->status === 'expired' ? 'selected' : '' }}>🔴 Expired</option>
                        </select>
                    </div>
                </div>
                <div class="info-group">
                    <div class="info-label">Last Updated</div>
                    <div class="info-value">{{ $member->walletPass->updated_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="sidebar">
        <div class="card">
            <div class="card-title">Pass Preview</div>
            <div class="wallet-preview">
                <div class="wallet-preview-header">
                    <span>APPLE ACCOUNT PASS</span>
                    <span>
                        @php
                            $passStatus = $member->walletPass ? $member->walletPass->status : 'active';
                        @endphp
                        {{ strtoupper($passStatus) }}
                    </span>
                </div>
                <div>
                    <div class="wallet-preview-name">{{ $member->full_name }}</div>
                    <div class="wallet-preview-id">#{{ $member->unique_member_id }}</div>
                </div>
            </div>

            <div class="btn-group">
                @if($member->walletPass && $member->walletPass->google_pass_url)
                    <a href="{{ $member->walletPass->google_pass_url }}" target="_blank" class="btn btn-google">
                        🔵 View Google Pass
                    </a>
                @endif

                <a href="{{ route('pass.download', ['id' => $member->id]) }}" class="btn btn-apple">
                    🍎 Download Apple Pass
                </a>

                @if($member->walletPass && $member->walletPass->google_pass_pdf_path)
                    <a href="{{ asset('storage/' . $member->walletPass->google_pass_pdf_path) }}" target="_blank" class="btn btn-pdf">
                        📄 Download PDF
                    </a>
                @else
                    <button onclick="regeneratePass({{ $member->id }})" class="btn btn-pdf">
                        📄 Generate PDF
                    </button>
                @endif

                <button onclick="regeneratePass({{ $member->id }})" class="btn btn-outline">
                    🔄 Regenerate All
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
async function updatePassStatus(memberId, status) {
    try {
        const response = await fetch('{{ route("admin.members.updatePassStatus") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ member_id: memberId, status: status })
        });
        const data = await response.json();
        if (data.success) {
            // No reload needed for the dropdown itself, but maybe for the badges
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Failed'));
        }
    } catch (e) {
        alert('❌ Error');
    }
}

async function regeneratePass(memberId) {
    if (!confirm('Regenerate wallet passes and resend email?')) return;
    
    try {
        const response = await fetch('{{ route("admin.members.regenerate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ member_id: memberId })
        });
        const data = await response.json();
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Failed'));
        }
    } catch (e) {
        alert('❌ Error');
    }
}
</script>
@endsection
