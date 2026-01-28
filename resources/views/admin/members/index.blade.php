@extends('layouts.admin')

@section('title', 'Members Management')

@section('styles')
<style>
    .section-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 15px;
        flex-wrap: wrap;
    }

    .search-box {
        display: flex;
        gap: 10px;
        flex: 1;
        max-width: 500px;
    }

    .search-box input {
        flex: 1;
        padding: 10px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .search-box select {
        padding: 10px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn:hover {
        opacity: 0.9;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th {
        background: #f5f7fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e5e7eb;
    }

    table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    table tr:hover {
        background: #f9fafb;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .actions {
        display: flex;
        gap: 8px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
        gap: 5px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        text-decoration: none;
        color: #333;
    }

    .pagination .active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
</style>
@endsection

@section('content')
    <div class="section-card">
        <div class="toolbar">
            <form method="GET" action="{{ route('admin.members.index') }}" class="search-box">
                <input type="text" name="search" placeholder="Search by name, email, or member ID..." value="{{ request('search') }}">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            <a href="{{ route('admin.members.create') }}" class="btn btn-success">➕ Add New Member</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Member Status</th>
                    <th>Pass Status</th>
                    <th>Wallet IDs</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    <tr>
                        <td><strong>{{ $member->unique_member_id }}</strong></td>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->email }}</td>
                        <td>{{ $member->mobile }}</td>
                        <td>
                            <span class="badge badge-{{ $member->status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                        <td>
                            @if($member->walletPass)
                                <select onchange="updatePassStatus({{ $member->id }}, this.value)" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px; font-weight: 500;">
                                    <option value="active" {{ $member->walletPass->status === 'active' ? 'selected' : '' }}>🟢 Active</option>
                                    <option value="pending" {{ $member->walletPass->status === 'pending' ? 'selected' : '' }}>🟡 Pending</option>
                                    <option value="expired" {{ $member->walletPass->status === 'expired' ? 'selected' : '' }}>🔴 Expired</option>
                                </select>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($member->walletPass)
                                <div style="font-size: 12px; line-height: 1.4;">
                                    @if($member->walletPass->google_object_id)
                                        <div><span title="Google Object ID">🔗</span> {{ \Illuminate\Support\Str::limit($member->walletPass->google_object_id, 15) }}</div>
                                    @endif
                                    @if($member->walletPass->apple_serial_number)
                                        <div><span title="Apple Serial Number">🍎</span> {{ \Illuminate\Support\Str::limit($member->walletPass->apple_serial_number, 15) }}</div>
                                    @endif
                                    @if(!$member->walletPass->google_object_id && !$member->walletPass->apple_serial_number)
                                        -
                                    @endif
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $member->created_at->format('M d, Y') }}</td>
                        <td>
                                <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-primary" style="background: #6366f1;">
                                    👁️ View
                                </a>
                                
                                {{-- Show "Add to Google Wallet" button only if it hasn't been "added" yet --}}
                                @if(!($member->walletPass && $member->walletPass->is_google_added))
                                    <a href="{{ route('google.wallet.redirect', ['id' => $member->id]) }}" 
                                       target="_blank" 
                                       class="btn wallet-btn" 
                                       style="background: #4285f4; color: white;"
                                       onclick="handleWalletClick()">
                                        Add to Google Wallet
                                    </a>
                                @endif
                                
                                {{-- Show "Add to Apple Wallet" button only if it hasn't been "added" yet --}}
                                @if(!($member->walletPass && $member->walletPass->is_apple_added))
                                    <a href="{{ route('pass.download', ['id' => $member->id]) }}" 
                                       class="btn wallet-btn" 
                                       style="background: #000000; color: white;"
                                       onclick="handleWalletClick(true)">
                                        Add to Apple Wallet
                                    </a>
                                @endif
                                
                                {{-- Show "View Pass" button only if google_pass_url exists --}}
                                <!-- @if($member->walletPass && $member->walletPass->google_pass_url)
                                    <a href="{{ $member->walletPass->google_pass_url }}" 
                                       target="_blank" 
                                       class="btn" 
                                       style="background: #4285f4; color: white;">
                                        👁️ View Pass
                                    </a>
                                @endif -->

                                @if($member->walletPass && $member->walletPass->google_pass_pdf_path)
                                    <a href="{{ asset('storage/' . $member->walletPass->google_pass_pdf_path) }}" 
                                       target="_blank" 
                                       class="btn" 
                                       style="background: #0f172a; color: white;">
                                        📄 PDF
                                    </a>
                                @endif

                                <button onclick="regeneratePass({{ $member->id }})" class="btn btn-success">
                                    🔄 Regenerate
                                </button>
                                <button onclick="deleteMember({{ $member->id }}, '{{ $member->unique_member_id }}')" class="btn btn-danger">
                                    🗑️ Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #666; padding: 40px;">
                            No members found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($members->hasPages())
            <div class="pagination">
                {{ $members->links() }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
// Refresh logic for wallet pass buttons
function handleWalletClick(isDownload = false) {
    if (isDownload) {
        // For Apple Wallet download, wait a bit then refresh
        setTimeout(() => {
            location.reload();
        }, 3000);
    } else {
        // For Google Wallet redirect (opens in new tab), 
        // refresh when user returns to this tab
        window.addEventListener('focus', function onFocus() {
            location.reload();
            window.removeEventListener('focus', onFocus);
        }, { once: true });
    }
}

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
            // Reload to show updated PDF link/status if needed, or just for confirmation
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Failed'));
        }
    } catch (e) {
        alert('❌ Error');
    }
}

async function regeneratePass(memberId) {
    if (!confirm('Regenerate wallet passes and resend email for this member?')) {
        return;
    }

    try {
        const response = await fetch('{{ route("admin.members.regenerate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ member_id: memberId })
        });

        const data = await response.json();

        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Failed to regenerate pass'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ An error occurred');
    }
}

async function deleteMember(memberId, memberIdStr) {
    if (!confirm(`Delete member ${memberIdStr}? This cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch('{{ route("admin.members.delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ member_id: memberId })
        });

        const data = await response.json();

        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'Failed to delete member'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ An error occurred');
    }
}
</script>
@endsection
