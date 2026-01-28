@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    body {
        font-family: 'Outfit', sans-serif;
        background: #f8fafc;
    }

    .dashboard-wrapper {
        padding: 20px;
    }

    /* Stats Section with Flexbox as requested */
    .stats-flex-container {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap; /* Allows wrapping on smaller screens */
    }

    .stat-card-premium {
        flex: 1;
        min-width: 240px;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: 20px;
        padding: 25px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card-premium:nth-child(2) { background: linear-gradient(135deg, #065f46 0%, #10b981 100%); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2); }
    .stat-card-premium:nth-child(3) { background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%); box-shadow: 0 10px 25px rgba(124, 58, 237, 0.2); }
    .stat-card-premium:nth-child(4) { background: linear-gradient(135deg, #d97706 0%, #fbbf24 100%); box-shadow: 0 10px 25px rgba(217, 119, 6, 0.2); }

    .stat-card-premium:hover {
        transform: translateY(-5px);
    }

    .stat-card-premium::after {
        content: "";
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .stat-label {
        font-size: 14px;
        font-weight: 500;
        opacity: 0.9;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
    }

    .stat-icon-bg {
        position: absolute;
        bottom: 15px;
        right: 20px;
        font-size: 40px;
        opacity: 0.2;
    }

    /* Section Cards - Keeping other things as requested */
    .content-box {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
        border: 1px solid #f1f5f9;
    }

    .content-box h2 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .content-box h2 span {
        color: #3b82f6;
    }

    /* Table Styling Refinement */
    .premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .premium-table th {
        background: #f8fafc;
        padding: 12px 20px;
        text-align: left;
        font-weight: 600;
        color: #64748b;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e2e8f0;
    }

    .premium-table td {
        padding: 16px 20px;
        background: white;
        border-bottom: 1px solid #f1f5f9;
    }

    .premium-table tr:hover td {
        background: #f8fafc;
    }

    .badge-modern {
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-active { background: #dcfce7; color: #166534; }
    .badge-pending { background: #fef3c7; color: #92400e; }

    .id-tag {
        font-family: 'JetBrains Mono', 'Courier New', Courier, monospace;
        background: #f1f5f9;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        color: #475569;
    }
</style>
@endsection

@section('content')
<div class="dashboard-wrapper">
    <!-- Premium Stats Row (Flexbox) -->
    <div class="stats-flex-container">
        <div class="stat-card-premium">
            <div class="stat-label">Total Members</div>
            <div class="stat-value">{{ number_format($statistics['total_members']) }}</div>
            <div class="stat-icon-bg">👥</div>
        </div>
        <div class="stat-card-premium">
            <div class="stat-label">Active Members</div>
            <div class="stat-value">{{ number_format($statistics['active_members']) }}</div>
            <div class="stat-icon-bg">✅</div>
        </div>
        <div class="stat-card-premium">
            <div class="stat-label">Today's Joins</div>
            <div class="stat-value">{{ number_format($statistics['today_registrations']) }}</div>
            <div class="stat-icon-bg">✨</div>
        </div>
        <div class="stat-card-premium">
            <div class="stat-label">This Week</div>
            <div class="stat-value">+{{ number_format($statistics['this_week_registrations']) }}</div>
            <div class="stat-icon-bg">📈</div>
        </div>
    </div>

    <!-- Recent Members Table -->
    <div class="content-box">
        <h2><span>📊</span> Recent Members</h2>
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Registration Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMembers as $member)
                    <tr>
                        <td><span class="id-tag">{{ $member->unique_member_id }}</span></td>
                        <td><strong>{{ $member->full_name }}</strong></td>
                        <td>{{ $member->email }}</td>
                        <td>
                            <span class="badge-modern badge-{{ $member->status === 'active' ? 'active' : 'pending' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                        <td style="color: #64748b;">{{ $member->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; padding: 40px;">No members yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Recent Activity Table -->
    <div class="content-box">
        <h2><span>🔔</span> Recent System Activity</h2>
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Admin User</th>
                    <th>Action Performed</th>
                    <th>Affected Entity</th>
                    <th>Time Elapsed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $activity)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px;">👤</div>
                                <strong>{{ $activity->admin->username ?? 'System' }}</strong>
                            </div>
                        </td>
                        <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $activity->action) }}</td>
                        <td><span class="id-tag">{{ ucfirst($activity->entity_type ?? 'N/A') }}</span></td>
                        <td style="color: #64748b;">{{ $activity->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 40px;">No recent activity logged</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
