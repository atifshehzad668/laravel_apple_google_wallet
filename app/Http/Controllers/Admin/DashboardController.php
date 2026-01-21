<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MemberService;
use App\Models\ActivityLog;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * Show admin dashboard.
     */
    public function index()
    {
        // Get statistics
        $statistics = $this->memberService->getMemberStatistics();

        // Get recent members
        $recentMembers = Member::where('status', '!=', 'deleted')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent activity logs
        $recentActivities = ActivityLog::with('admin')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        return view('admin.dashboard', compact('statistics', 'recentMembers', 'recentActivities'));
    }
}
