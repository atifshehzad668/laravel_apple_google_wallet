@extends('layouts.admin')

@section('title', 'Dashboard')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .stat-card h3 {
        font-size: 14px;
        color: #666;
        margin-bottom: 8px;
    }

    .stat-card .value {
        font-size: 32px;
        font-weight: 700;
        color: #1e3a8a;
    }

    .section-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .section-card h2 {
        font-size: 20px;
        color: #1e3a8a;
        margin-bottom: 20px;
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
</style>
@endsection

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Members</h3>
            <div class="value">{{ $statistics['total_members'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Active Members</h3>
            <div class="value">{{ $statistics['active_members'] }}</div>
        </div>
        <div class="stat-card">
            <h3>Today's Registrations</h3>
            <div class="value">{{ $statistics['today_registrations'] }}</div>
        </div>
        <div class="stat-card">
            <h3>This Week</h3>
            <div class="value">{{ $statistics['this_week_registrations'] }}</div>
        </div>
    </div>

    <div class="section-card">
        <h2>Recent Members</h2>
        <table>
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMembers as $member)
                    <tr>
                        <td><strong>{{ $member->unique_member_id }}</strong></td>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->email }}</td>
                        <td>
                            <span class="badge badge-{{ $member->status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                        <td>{{ $member->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">No members yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section-card">
        <h2>Recent Activity</h2>
        <table>
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $activity)
                    <tr>
                        <td>{{ $activity->admin->username ?? 'System' }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($activity->action)) }}</td>
                        <td>{{ ucfirst($activity->entity_type ?? '-') }}</td>
                        <td>{{ $activity->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #666;">No activity yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
